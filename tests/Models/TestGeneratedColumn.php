<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id DEFINED BY `id INTEGER PRIMARY KEY AUTOINCREMENT`
 * @property string name DEFINED BY `name VARCHAR(100) NOT NULL UNIQUE`
 * @property int age DEFINED BY `age SMALLINT NOT NULL`
 * @property bool is_adult DEFINED BY `is_adult BOOLEAN ALWAYS GENERATED AS (age >= 21)`
*/
class TestGeneratedColumn extends AbstractModel
{
    public static function getTable(): string
    {
        return "test_generated_column";
    }

    public static function getPrimaryKey(): string|null
    {
        return 'id';
    }

    public static function getFields(): array
    {
        return [
            'id' => (new DatabaseField('id'))->isGenerated()->hasDefault(true)->setNullable(true)->setType(DatabaseField::INTEGER),
			'name' => (new DatabaseField('name'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::STRING),
			'age' => (new DatabaseField('age'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::INTEGER),
			'is_adult' => (new DatabaseField('is_adult'))->isGenerated()->hasDefault(true)->setNullable(true)->setType(DatabaseField::BOOLEAN)
        ];
    }
}
