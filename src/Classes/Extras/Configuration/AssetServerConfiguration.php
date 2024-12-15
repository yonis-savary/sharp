<?php

namespace YonisSavary\Sharp\Classes\Extras\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class AssetServerConfiguration
{
    use ConfigurationElement;

    /**
     * @param bool $enabled Is the service enabled ?
     * @param bool $cached When `true`, will cache the `request-url => file-path` data
     * @param string $url Route URL to the service
     * @param array $middlewares Middlewares to use
     * @param bool $maxAge Used to set the `Cache-control` max age value (client caching)
     * @param array $nodePackages Node package names to serve
     */
    public function __construct(
        public readonly bool $enabled = true,
        public readonly bool $cached = true,
        public readonly string $url = '/assets/{any:filename}',
        public readonly array $middlewares = [],
        public readonly int|false $maxAge = false,
        public readonly array $nodePackages = []
    )
    {}
}