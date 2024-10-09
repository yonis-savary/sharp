<?php

namespace YonisSavary\Sharp\Classes\Data\Classes;

use YonisSavary\Sharp\Classes\Data\Database;

class QueryConditionRaw
{
    public function __construct(
        public string $condition,
        public array $context=[]
    ){}

    public function __toString()
    {
        return Database::getInstance()->build("($this->condition)", $this->context);
    }
}