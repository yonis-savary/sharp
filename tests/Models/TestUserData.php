<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;

class TestUserData
{
    use \YonisSavary\Sharp\Classes\Data\Model;

    public static function getTable(): string
    {
        return "test_user_data";
    }

    public static function getPrimaryKey(): string|null
    {
        return 'id';
    }

    public static function getFields(): array
    {
        return [
            'id' => (new DatabaseField('id'))->hasDefault(false)->setType(DatabaseField::INTEGER),
			'fk_user' => (new DatabaseField('fk_user'))->hasDefault(false)->setType(DatabaseField::INTEGER)->references(TestUser::class, 'id'),
			'data' => (new DatabaseField('data'))->hasDefault(false)->setType(DatabaseField::STRING)
        ];
    }
}
