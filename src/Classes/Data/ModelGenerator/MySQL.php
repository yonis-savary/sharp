<?php

namespace YonisSavary\Sharp\Classes\Data\ModelGenerator;

use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Core\Utils;

class MySQL extends GeneratorDriver
{
    public function listTables(): array
    {
        $db = $this->connection;
        $res = $db->query("SHOW TABLES");

        foreach ($res as &$arr)
            $arr = array_values($arr)[0];

        return $res;
    }

    protected function getFieldDescription(array $fieldDescription, array $foreignKey=null, mixed &$primaryKey): string
    {
        list($field, $type, $null, $key, $default, $extras) = array_values($fieldDescription);
        $string = "'$field' => (new DatabaseField('$field'))";


        $classType = "STRING";
        if (preg_match("/int\(/", $type))           $classType = "INTEGER";
        if (preg_match("/float\(/", $type))         $classType = "FLOAT";
        if (preg_match("/smallint\(1\)/", $type))   $classType = "BOOLEAN";
        if (preg_match("/decimal/", $type))         $classType = "DECIMAL";
        $string .= "->setType(DatabaseField::$classType)";

        $string .= "->setNullable(". ($null=="YES" ? "true": "false") .")";

        $lowerExtras = strtolower($extras);
        $isGenerated = str_contains($lowerExtras, 'auto_increment') || str_contains($lowerExtras, "generated");

        if ($isGenerated)
            $string .= "->isGenerated()";

        $hasDefault = $null || $default || $isGenerated;
        $string .= "->hasDefault(". ($hasDefault ? "true": "false") .")";

        if ($ref = $foreignKey[$field] ?? false)
            $string .= "->references(".$this->sqlToPHPName($ref[0])."::class, '$ref[1]')";

        if ($key === "PRI")
            $primaryKey ??= $field;

        return $string;
    }


    protected function getFieldDoc(array $fieldDescription, array $foreignKey=null, string $namespace): string
    {
        list($field, $type, $null, $key, $default, $extras) = array_values($fieldDescription);

        $type = "string";
        if (preg_match("/int\(/", $type))           $type = "int";
        if (preg_match("/float\(/", $type))         $type = "float";
        if (preg_match("/smallint\(1\)/", $type))   $type = "float";

        if ($ref = $foreignKey[$field] ?? false)
            $type =  "\\" . $namespace . "\\" .  $this->sqlToPHPName($ref[0]);

        return " * @property $type \$$field";
    }

    public function generate(string $table, string $targetApplication, string $modelNamespace=null): void
    {
        $db = $this->connection;
        $databaseName = $db->database;

        $classBasename = $this->sqlToPHPName($table);

        $fileName = "$classBasename.php";
        $fileDir = Utils::joinPath($targetApplication, "Models");
        $filePath = Utils::joinPath($fileDir, $fileName);

        if (!is_dir($fileDir)) mkdir($fileDir);
        $namespace = $modelNamespace ?? Utils::pathToNamespace($fileDir);

        $foreignKeysRaw = $db->query(
            "SELECT
                COLUMN_NAME as source_field,
                REFERENCED_TABLE_NAME as target_table,
                REFERENCED_COLUMN_NAME as target_field
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = {}
            AND TABLE_NAME = {}
            AND REFERENCED_COLUMN_NAME IS NOT NULL;
        ", [$databaseName, $table]);

        $foreignKeys = [];
        $usedModels = [];
        foreach ($foreignKeysRaw as $row)
        {
            $foreignKeys[$row["source_field"]] = [$row["target_table"], $row["target_field"]];
            $usedModels[] = $row["target_table"];
        }

        $usedModels = ObjectArray::fromArray($usedModels)
        ->map(function($model) use ($fileDir) {
            $model = $this->sqlToPHPName($model);
            $model = Utils::joinPath($fileDir, $model);
            $model = Utils::pathToNamespace($model);
            return "use $model;";
        })
        ->filter(fn($x) => !str_contains($x, "$namespace\\$classBasename"))
        ->unique()
        ->collect();

        $primaryKey = null;

        $description = ObjectArray::fromArray($db->query("DESCRIBE `$table`"))
        ->map(function($e) use ($foreignKeys, &$primaryKey) {
            return $this->getFieldDescription($e, $foreignKeys, $primaryKey);
        })
        ->collect();

        $documentation = ObjectArray::fromArray($db->query("DESCRIBE `$table`"))
        ->map(fn($e) => $this->getFieldDoc($e, $foreignKeys, $namespace))
        ->join("\n");


        file_put_contents($filePath, Terminal::stringToFile(
        "<?php

        namespace $namespace;

        use ".DatabaseField::class.";
        use ".AbstractModel::class.";
        ".join("\n", $usedModels)."

        /**
        $documentation
        */
        class $classBasename extends AbstractModel
        {
            public static function getTable(): string
            {
                return \"$table\";
            }

            public static function getPrimaryKey(): string
            {
                return ". ($primaryKey ? "'$primaryKey'" : 'null') .";
            }

            public static function getFields(): array
            {
                return [
                    ".join(",\n\t\t\t", $description)."
                ];
            }
        }
        ",
        2));
    }
}