<?php

namespace YonisSavary\Sharp\Commands\Cache;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Core\Autoloader;

class CacheAutoload extends Command
{
    public function getHelp(): string
    {
        return "Put your autoloader's data in cache for better performances";
    }

    public function __invoke(Args $args)
    {
        Autoloader::writeAutoloadCache();
        $this->log(
            "File written : " . Autoloader::CACHE_FILE,
            "Delete it to switch to classic autoload",
        );
    }
}