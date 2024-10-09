<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;

class TestSampleData
{
    use \YonisSavary\Sharp\Classes\Data\Model;

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
            'id' => (new DatabaseField('id'))->hasDefault(false)->setType(DatabaseField::INTEGER),
			'name' => (new DatabaseField('name'))->hasDefault(false)->setType(DatabaseField::STRING),
			'birth_year' => (new DatabaseField('birth_year'))->hasDefault(false)->setType(DatabaseField::INTEGER)
        ];
    }
}
