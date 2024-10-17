<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id
 * @property string name
 * @property int birth_year
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
