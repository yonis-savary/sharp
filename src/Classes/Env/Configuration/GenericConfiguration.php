<?php

namespace YonisSavary\Sharp\Classes\Env\Configuration;

class GenericConfiguration
{
    public function __construct(
        public string $name,
        public mixed $value
    ){}
}