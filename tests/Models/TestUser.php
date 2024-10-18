<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id DEFINED BY `id INTEGER PRIMARY KEY AUTOINCREMENT`
 * @property string login DEFINED BY `login VARCHAR(100) NOT NULL UNIQUE`
 * @property string password DEFINED BY `password VARCHAR(100) NOT NULL`
 * @property string salt DEFINED BY `salt VARCHAR(100) NOT NULL`
 * @property bool blocked DEFINED BY `blocked BOOLEAN DEFAULT FALSE`
*/
class TestUser extends AbstractModel
{
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
            'id' => (new DatabaseField('id'))->isGenerated()->hasDefault(true)->setNullable(true)->setType(DatabaseField::INTEGER),
			'login' => (new DatabaseField('login'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::STRING),
			'password' => (new DatabaseField('password'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::STRING),
			'salt' => (new DatabaseField('salt'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::STRING),
			'blocked' => (new DatabaseField('blocked'))->hasDefault(true)->setNullable(true)->setType(DatabaseField::BOOLEAN)
        ];
    }
}
