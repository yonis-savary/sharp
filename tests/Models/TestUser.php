<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;

class TestUser
{
    use \YonisSavary\Sharp\Classes\Data\Model;

    public static function getTable(): string
    {
        return "test_user";
    }

    public static function getPrimaryKey(): string|null
    {
        return 'id';
    }

    public static function getFields(): array
    {
        return [
            'id' => (new DatabaseField('id'))->hasDefault(false)->setType(DatabaseField::INTEGER),
			'login' => (new DatabaseField('login'))->hasDefault(false)->setType(DatabaseField::STRING),
			'password' => (new DatabaseField('password'))->hasDefault(false)->setType(DatabaseField::STRING),
			'salt' => (new DatabaseField('salt'))->hasDefault(false)->setType(DatabaseField::STRING),
			'blocked' => (new DatabaseField('blocked'))->hasDefault(true)->setType(DatabaseField::BOOLEAN)
        ];
    }
}
