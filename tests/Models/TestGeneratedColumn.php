<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id
 * @property string name
 * @property int age
 * @property bool is_adult
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
