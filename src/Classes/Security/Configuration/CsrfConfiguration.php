<?php

namespace YonisSavary\Sharp\Classes\Security\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class CsrfConfiguration
{
    use ConfigurationElement;

    public function __construct(
        public readonly string $htmlInputName = "csrf-token"
    ){}
}