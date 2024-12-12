<?php

namespace YonisSavary\Sharp\Classes\Data;

use Exception;
use InvalidArgumentException;
use JsonSerializable;
use stdClass;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Core\Utils;

/**
 * Classes that uses `Model` represents tables from your Database
 */
abstract class AbstractModel implements JsonSerializable
{
    protected stdClass $originalData;
    protected stdClass $data;

    /** @var stdClass<Model> */
    protected stdClass $foreignObjects;

    protected bool $linkedToDB = false;
    protected ?Database $linkedToDBInstance = null;


    public function __construct(array $data=[], bool $linkedToDB=false)
    {
        $this->foreignObjects = new stdClass;
        $this->linkedToDB = $linkedToDB;

        $this->data = $data ? (object) $data: new stdClass;
    }

    public function &getOrCreateForeignObject(string $key, AbstractModel $model)
    {
        if (!property_exists($this->foreignObjects, $key))
            $this->foreignObjects->$key = $model;

        return $this->foreignObjects->$key;
    }


    public function setLinked(bool $linked=true, Database $database=null)
    {
        $this->linkedToDB = $linked;
        $this->linkedToDBInstance = $database;
    }


    public function &__get(string $name): mixed
    {
        if ($this->linkedToDB)
        {
            /** @var self */
            $self = get_called_class();
            $primaryKey = $self::getPrimaryKey();
            $object = $self::findId($this->data->$primaryKey);

            $this->data = $object->data;
        }

        if ($name === 'data')
            return $this->data;

        if (property_exists($this->foreignObjects, $name))
            return $this->foreignObjects->$name;

        if (property_exists($this->data, $name))
            return $this->data->$name;

        throw new InvalidArgumentException("Unknown property [$name]");
    }


    public function __set(string $name, mixed $value)
    {
        $this->data->$name = $value;

        if ($this->linkedToDB)
            $this->save();
    }


    public function toArray(): array
    {
        $result = [ 'data' => (array) $this->data ];

        foreach ($this->foreignObjects as $key => $model)
            $result[$key] = $model->toArray();

        return $result;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }


    public function save(Database $database=null)
    {
        if ((!$database) && $this->linkedToDBInstance)
            $database = $this->linkedToDBInstance;

        /** @var self */
        $self = get_called_class();

        $primaryKey = ($self)::getPrimaryKey();
        $fields = ($self)::getFields();

        $currentData = (array) $this->data;

        if (! $id = $currentData[$primaryKey] ?? false)
            throw new Exception('Cannot update '. $self::getTable() ." without $primaryKey");

        $newData = [];

        foreach($currentData as $fieldName => $value)
        {
            if ($field = $fields[$fieldName] ?? false)
            {
                if ($field->validateUpdate($value))
                    $newData[$fieldName] = $value;
            }
        }

        if (count($newData))
            ($self)::updateRow($id, $newData, $database);

    }



    /**
     * @return string The table name in your database
     */
    public static abstract function getTable(): string;

    /**
     * @return string|null The primary key field name (or null if none)
     */
    public static abstract function getPrimaryKey(): string|null;

    /**
     * @return array<string,DatabaseField> Associative array with name => field description (DatabaseField object)
     */
    public static abstract function getFields(): array;


    final public static function getFieldNames(): array
    {
        return array_keys((get_called_class())::getFields());
    }

    public static function getInsertables(): array
    {
        $self = get_called_class();
        $primaryKey = $self::getPrimaryKey();

        return ObjectArray::fromArray($self::getFieldNames())
        ->filter(fn($field) => $field != $primaryKey)
        ->collect();
    }

    /**
     * Start a ModelQuery to insert values in the model's table
     */
    public static function insert(array $insertFields=null): ModelQuery
    {
        $self = get_called_class();
        $query = new ModelQuery($self, ModelQuery::INSERT);
        $query->setInsertField($insertFields ?? $self::getFieldNames());

        return $query;
    }

    /**
     * Start a ModelQuery to select rows from the model's table
     */
    public static function select(bool $recursive=true, array $foreignKeyIgnores=[]): ModelQuery
    {
        $self = get_called_class();
        $query = new ModelQuery($self, ModelQuery::SELECT);
        $query->exploreModel($self, $recursive, $foreignKeyIgnores);

        return $query;
    }

    /**
     * Select every row that respects given conditions
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $recursive Explore foreign keys to fetch references
     * @param array $foreignKeyIgnores List of foreign keys to ignore while exploring model as 'table&foreign_key_column'
     * @return array<static> Array of result rows
     * @example base `Model::selectWhere(['id' => 309, 'user' => 585])`
     */
    public static function selectWhere(array $conditions=[], bool $recursive=true, array $foreignKeyIgnores=[], Database $database=null): array
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = (get_called_class())::select($recursive, $foreignKeyIgnores);

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        return $query->fetch($database);
    }

    /**
     * Start a ModelQuery to update row(s) of the model's table
     */
    public static function update(): ModelQuery
    {
        return new ModelQuery(get_called_class(), ModelQuery::UPDATE);
    }

    /**
     * Start a ModelQuery to delete row(s) from the model's table
     */
    public static function delete(): ModelQuery
    {
        return new ModelQuery(get_called_class(), ModelQuery::DELETE);
    }

    /**
     * Insert a row of data in the model's table
     *
     * @param array $data Associative array (with `field => value`) to insert
     * @param Database $database Database to use (global instance if `null`)
     * @return int|false Return the inserted Id or false on failure
     */
    public static function insertArray(array $data, Database $database=null): int|false
    {
        if (!Utils::isAssoc($data))
            throw new InvalidArgumentException('Given data must be an associative array !');

        $self = get_called_class();
        $dataFields = array_keys($data);
        $modelFields = $self::getFieldNames();

        $invalidFields = array_diff($dataFields, $modelFields);
        if (count($invalidFields))
        {
            $invalidFields = join(', ', $invalidFields);
            throw new InvalidArgumentException($self . " model does not contains these fields: $invalidFields");
        }

        $database ??= Database::getInstance();

        $insert = new ModelQuery(get_called_class(), ModelQuery::INSERT);
        $insert->setInsertField($dataFields);
        $insert->insertValues(array_values($data));
        $insert->fetch($database);

        return $database->lastInsertId();
    }

    /**
     * Select a row where the primary key is the one given
     *
     * @param mixed $id Id to select
     * @param bool $explore Explore foreign keys to fetch references
     * @return ?static Matching row or `null`
     */
    public static function findId(mixed $id, bool $explore=true, Database $database=null): ?self
    {
        $self = get_called_class();
        return $self::findWhere([$self::getPrimaryKey() => $id], $explore, $database);
    }

    /**
     * Select the first row where `$column` equal `$value`
     *
     * @param mixed $column Filter column
     * @param mixed $value Value to match
     * @param bool $explore Explore foreign keys to fetch references
     * @return ?static Matching row or `null`
     */
    public static function find(string $column, mixed $value, bool $explore=true, Database $database=null): ?self
    {
        $self = get_called_class();
        return $self::findWhere([$column => $value], $explore, $database);
    }


    /**
     * Find a row with specified data or create one
     *
     * @param array $data specified data
     * @param bool $recursive Fetch foreign model data when `true`
     * @param Database $database Database to use when fetching/creating data
     * @return static New or existing row instance
     */
    public static function findOrCreate(array $data, bool $recursive=true, Database $database=null): self
    {
        $self = get_called_class();
        $database ??= Database::getInstance();

        $primaryKey = $self::getPrimaryKey();
        if (array_key_exists($primaryKey, $data))
            Logger::getInstance()->warning("$primaryKey key data was used with ".self::getTable()." findOrCreate() method");

        if ($existing = $self::findWhere($data, $recursive, $database))
            return $existing;

        $id = $self::insertArray($data, $database);
        return $self::findId($id, $recursive, $database);
    }

    /**
     * Select the first row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $explore Explore foreign keys to fetch references
     * @return ?static Matching row or `null`
     * @example base `Model::findWhere(['id' => 309, 'user' => 585])`
     */
    public static function findWhere(array $conditions, bool $explore=true, Database $database=null): ?self
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = (get_called_class())::select($explore);

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        return $query->first($database);
    }


    /**
     * Check the existence of any row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     */
    public static function existsWhere(array $condition, bool $explore=false, Database $database=null): bool
    {
        $self = get_called_class();
        return $self::findWhere($condition, $explore, $database) !== null;
    }

    /**
     * Check the existence of any row where the primary key matches the given one
     *
     * @param array $conditions Column conditions as <column> => <value>
     */
    public static function idExists($idOrPrimaryKeyValue, Database $database=null): bool
    {
        $self = get_called_class();
        return $self::existsWhere([$self::getPrimaryKey() => $idOrPrimaryKeyValue], false, $database);
    }

    /**
     * Return a new `ModelQuery` to update a specific row
     *
     * @param mixed $id Id to select
     * @return ModelQuery Base query to work with
     */
    public static function updateId(mixed $id): ModelQuery
    {
        $self = get_called_class();
        return $self::update()->where($self::getPrimaryKey(), $id);
    }

    /**
     * Directly update a row in the model table
     *
     * @param mixed $id Unique value of the primary key field
     * @param array $columns Updated columns, associative array as `field => new value`
     */
    public static function updateRow(mixed $id, array $columns, Database $database=null): void
    {
        $query = (get_called_class())::updateId($id);

        foreach ($columns as $field => $value)
            $query->set($field, $value);

        $query->fetch($database);
    }

    /**
     * Delete specific row following the primary key
     *
     * @param mixed $id Id/primary key to select
     */
    public static function deleteId(mixed $id, Database $database=null): void
    {
        $self = get_called_class();

        $self::delete()
        ->where($self::getPrimaryKey(), $id)
        ->fetch($database);
    }

    /**
     * Delete every row where conditions from $condition are matched
     *
     * @param array $conditions Column conditions as <column> => <value>
     * @param bool $explore Explore foreign keys to fetch references
     * @example base `Model::deleteWhere(['id' => 309, 'user' => 585])`
     */
    public static function deleteWhere(array $conditions, Database $database=null): void
    {
        if (!Utils::isAssoc($conditions))
            throw new InvalidArgumentException('$conditions must be an associative array as <column> => <value>');

        $query = (get_called_class())::delete();

        foreach ($conditions as $column => $value)
            $query->where($column, $value);

        $query->fetch($database);
    }


    public static function validate(array $data=null): bool
    {
        foreach ((get_called_class())::getFields() as $fieldName => $fieldObject)
        {
            $value = $data[$fieldName] ?? null;
            if (!$fieldObject->validate($value))
                return false;
        }
        return true;
    }
}