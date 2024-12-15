<?php

namespace YonisSavary\Sharp\Classes\Http\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class RequestConfiguration
{
    use ConfigurationElement;

    /**
     * @param bool $typedParameters When `true`, will parse parameters with values such as `"true"`, `"false"` and `"null"`
     */
    public function __construct(
        public readonly bool $typedParameters = true
    ){}
}