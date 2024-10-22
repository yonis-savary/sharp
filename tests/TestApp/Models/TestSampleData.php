<?php

namespace YonisSavary\Sharp\Tests\TestApp\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id DEFINED BY `id INTEGER PRIMARY KEY AUTOINCREMENT`
 * @property string name DEFINED BY `name VARCHAR(100) NOT NULL UNIQUE`
 * @property int birth_year DEFINED BY `birth_year INT NOT NULL`
*/
class TestSampleData extends AbstractModel
{
    public static function getTable(): string
    {
        return "test_sample_data";
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
			'birth_year' => (new DatabaseField('birth_year'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::INTEGER)
        ];
    }
}
