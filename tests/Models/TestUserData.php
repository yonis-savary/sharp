<?php

namespace YonisSavary\Sharp\Tests\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id
 * @property \YonisSavary\Sharp\Tests\Models\TestUser fk_user
 * @property string data
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
