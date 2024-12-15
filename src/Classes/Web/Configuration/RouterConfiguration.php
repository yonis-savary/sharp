<?php

namespace YonisSavary\Sharp\Classes\Web\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class RouterConfiguration
{
    use ConfigurationElement;

    /**
     * @param bool $cached When `true` will cache the view file
     * @param bool $quickRouting When `true`, will use the quick-routing feature to optimize serving time
     */
    public function __construct(
        public readonly bool $cached = false,
        public readonly bool $quickRouting = false
    ){}
}