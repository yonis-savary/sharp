<?php

namespace YonisSavary\Sharp\Tests\TestApp\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id DEFINED BY `id INTEGER PRIMARY KEY AUTOINCREMENT`
 * @property \YonisSavary\Sharp\Tests\TestApp\Models\TestUser fk_user DEFINED BY `fk_user INTEGER NOT NULL REFERENCES test_user(id) ON DELETE CASCADE`
 * @property string data DEFINED BY `data VARCHAR(200)`
*/
class TestUserData extends AbstractModel
{
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
            'id' => (new DatabaseField('id'))->isGenerated()->hasDefault(true)->setNullable(true)->setType(DatabaseField::INTEGER),
			'fk_user' => (new DatabaseField('fk_user'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::INTEGER)->references(TestUser::class, 'id'),
			'data' => (new DatabaseField('data'))->hasDefault(false)->setNullable(true)->setType(DatabaseField::STRING)
        ];
    }
}
