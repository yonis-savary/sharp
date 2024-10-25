<?php

namespace YonisSavary\Sharp\Tests\Root\TestApp\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property int id DEFINED BY `id INTEGER PRIMARY KEY AUTOINCREMENT`
 * @property string name DEFINED BY `name VARCHAR(50) NOT NULL UNIQUE`
 * @property int episode_number DEFINED BY `episode_number SMALLINT DEFAULT 1`
*/
class TestTvShow extends AbstractModel
{
    public static function getTable(): string
    {
        return "test_tv_show";
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
			'episode_number' => (new DatabaseField('episode_number'))->hasDefault(true)->setNullable(true)->setType(DatabaseField::INTEGER)
        ];
    }
}
