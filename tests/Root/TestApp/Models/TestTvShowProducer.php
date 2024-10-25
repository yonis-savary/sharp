<?php

namespace YonisSavary\Sharp\Tests\Root\TestApp\Models;

use YonisSavary\Sharp\Classes\Data\DatabaseField;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * @property \YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShow tv_show DEFINED BY `tv_show INT NOT NULL REFERENCES test_tv_show(id) ON DELETE CASCADE`
 * @property string name DEFINED BY `name VARCHAR(100) NOT NULL`
*/
class TestTvShowProducer extends AbstractModel
{
    public static function getTable(): string
    {
        return "test_tv_show_producer";
    }

    public static function getPrimaryKey(): string|null
    {
        return 'id';
    }

    public static function getFields(): array
    {
        return [
            'tv_show' => (new DatabaseField('tv_show'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::INTEGER)->references(TestTvShow::class, 'id'),
			'name' => (new DatabaseField('name'))->hasDefault(false)->setNullable(false)->setType(DatabaseField::STRING)
        ];
    }
}
