<?php

namespace YonisSavary\Sharp\Classes\Data\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class ModelQueryConfiguration
{
    use ConfigurationElement;

    /**
     * @param int $joinLimit Your DBMS join number hard limit
     */
    public function __construct(
        public readonly int $joinLimit = 50
    ){}
}