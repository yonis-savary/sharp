<?php

namespace YonisSavary\Sharp\Core\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class Environmnent
{
    use ConfigurationElement;

    public function __construct(
        public string $environment="dev"
    )
    {
        $this->environment = strtolower($this->environment);
    }
}