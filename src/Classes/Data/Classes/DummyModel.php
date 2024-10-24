<?php

namespace YonisSavary\Sharp\Classes\Data\Classes;

use YonisSavary\Sharp\Classes\Data\AbstractModel;

/**
 * This model can be used to store data and do
 * not represent any particular table in the database
 */
class DummyModel extends AbstractModel {
    public static function getTable(): string { return "dummy-class"; }
    public static function getPrimaryKey(): ?string { return null; }
    public static function getFields(): array { return []; }
};