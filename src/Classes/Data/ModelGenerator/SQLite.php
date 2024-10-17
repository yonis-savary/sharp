<?php

namespace YonisSavary\Sharp\Classes\Data\ModelGenerator;

use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Core\Utils;

class SQLite extends GeneratorDriver
{
    // https://www.sqlite.org/datatype3.html
    const TYPES_REGEX = [
        'INT',
        'INTEGER',
        'TINYINT',
        'SMALLINT',
        'MEDIUMINT',
        'BIGINT',
        'UNSIGNED BIG INT',
        'INT2',
        'INT8',
        'CHARACTER\(\d+\)',
        'VARCHAR\(\d+\)',
        'VARYING CHARACTER\(\d+\)',
        'NCHAR\(\d+\)',
        'NATIVE CHARACTER\(\d+\)',
        'NVARCHAR\(\d+\)',
        'TEXT',
        'CLOB',
        'BLOB',
        'REAL',
        'DOUBLE',
        'DOUBLE PRECISION',
        'FLOAT',
        'NUMERIC',
        'DECIMAL\(\d+ ?, ?\d+\)',
        'BOOLEAN',
        'DATE',
        'DATETIME',
        'TIMESTAMP'
    ];

    protected array $schemaCache = [];
    protected ?string $currentPrimaryKey = null;
    protected ?string $currentDocumentation = "";
    protected array $fieldExtras = [];

    public function listTables(): array
    {
        $res = $this->connection->query("SELECT * FROM sqlite_master");

        $tables = [];
        foreach ($res as &$row)
        {
            if (str_starts_with($row["name"], "sqlite_"))
                continue;

            $table = $row["name"];
            $this->schemaCache[$table] = $row["sql"];
            $tables[] = $table;
        }

        return $tables;
    }

    public function getTableFields(string $createTableScript, string $namespace): array
    {
        $sql = $createTableScript;

        $sql = str_replace("\n", ' ', $sql);
        $sql = preg_replace('/^CREATE TABLE .+? \(/', '', $sql);
        $sql = preg_replace('/\)\s*$/s', '', $sql);

        $parenthesisCount = 0;
        $lines = [];
        $currentLine = '';
        $chars = str_split($sql);
        for ($i=0; $i<count($chars); $i++)
        {
            $char = $chars[$i];

            if ($char == '(')
                $parenthesisCount++;
            else if ($char == ')')
                $parenthesisCount--;

            if ($char === ',' && $parenthesisCount==0)
            {
                $lines[] = $currentLine;
                $currentLine = '';
            }
            else
            {
                $currentLine.=$char;
            }
        }
        $lines[]= $currentLine;

        return ObjectArray::fromArray($lines)
        ->map(fn($line) => $this->lineToField($line, $namespace))
        ->filter()
        ->collect();
    }

    public function lineToField(string $sqlLine, string $namespace): ?array
    {
        $sqlLine = trim($sqlLine);

        $field = "";

        $matches = [];
        $typesNames = join('|', self::TYPES_REGEX);
        $columnRegex = "/^(.+?) ($typesNames)/";

        $description = "";

        if (preg_match($columnRegex, $sqlLine, $matches))
        {
            $fieldName = $matches[1];
            $field = "'$fieldName' => (new DatabaseField('$fieldName'))";

            $isGenerated = str_contains($sqlLine, "AUTOINCREMENT") || str_contains($sqlLine, "GENERATED");
            if ($isGenerated)
                $field .= "->isGenerated()";

            $hasDefault = $isGenerated || str_contains($sqlLine, 'DEFAULT');
            $field .= "->hasDefault(".($hasDefault ? "true": "false").")";
            if (str_contains($sqlLine, 'PRIMARY KEY'))
                $this->currentPrimaryKey = $matches[1];

            $canBeNull = !str_contains($sqlLine, "NOT NULL");
            $field .= "->setNullable(". ($canBeNull ? "true": "false") .")";

            $descriptionType = "string";
            $type = $matches[2];
            $classType = "STRING";
            if (preg_match("/smallint\(1\)/i", $type))  { $classType = "BOOLEAN"; $descriptionType = "bool"; }
            else if (preg_match("/bool/i", $type))      { $classType = "BOOLEAN"; $descriptionType = "bool"; }
            else if (preg_match("/int/i", $type))       { $classType = "INTEGER"; $descriptionType = "int"; }
            else if (preg_match("/float\(/i", $type))   { $classType = "FLOAT"  ; $descriptionType = "float"; }
            else if (preg_match("/decimal/i", $type))   { $classType = "DECIMAL"; $descriptionType = "string"; }
            $field .= "->setType(DatabaseField::$classType)";

            $matches = [];
            if (preg_match('/REFERENCES (.+?)\((.+?)\)/', $sqlLine, $matches))
            {
                $refClassName = $this->sqlToPHPName($matches[1]);
                $field .= "->references($refClassName::class, '".$matches[2]."')";
                $descriptionType = "\\$namespace\\$refClassName";
            }

            $this->currentDocumentation .= " * @property $descriptionType $fieldName\n";

            return [$fieldName, $field];
        }

        $matches = [];
        if (preg_match('/^FOREIGN KEY \((.+?)\) REFERENCES (.+?)\((.+?)\)$/', $sqlLine, $matches))
            $this->fieldExtras[$matches[1]] = "->references(".$this->sqlToPHPName($matches[2])."::class, '".$matches[3]."')";

        return null;
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
        $this->currentDocumentation = "";
        $classBasename = $this->sqlToPHPName($table);

        $fileName = "$classBasename.php";
        $fileDir = Utils::joinPath($targetApplication, "Models");
        $filePath = Utils::joinPath($fileDir, $fileName);

        if (!is_dir($fileDir))
            mkdir($fileDir);

        $namespace = $modelNamespace ?? Utils::pathToNamespace($fileDir);

        $descriptionRaw = $this->schemaCache[$table];
        $fields = $this->getTableFields($descriptionRaw, $namespace);

        foreach ($fields as &$field)
        {
            list($name, $string) = $field;
            $string .= $this->fieldExtras[$name] ?? "";
            $field = $string;
        }

        file_put_contents($filePath, Terminal::stringToFile(
        "<?php

        namespace $namespace;

        use ".DatabaseField::class.";
        use ".AbstractModel::class.";

        /**
         ".trim($this->currentDocumentation)."
        */
        class $classBasename extends AbstractModel
        {
            public static function getTable(): string
            {
                return \"$table\";
            }

            public static function getPrimaryKey(): string|null
            {
                return '".$this->currentPrimaryKey."';
            }

            public static function getFields(): array
            {
                return [
                    ".join(",\n\t\t\t", $fields)."
                ];
            }
        }
        ",
        2));
    }
}