<?php

namespace YonisSavary\Sharp\Classes\Web\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class RenderedConfiguration
{
    use ConfigurationElement;

    /**
     * @param bool $cached When `true` will cache the view file
     * @param string $fileExtension Views file extension to filter when looking for view files
     */
    public function __construct(
        public readonly bool $cached = false,
        public readonly string $fileExtension = '.php'
    ){}
}