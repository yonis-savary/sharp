<?php

namespace YonisSavary\Sharp\Commands\Cache;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;

class CacheAutoload extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Put your autoloader's data in cache for better performances";
    }

    public function execute(Args $args): int
    {
        Autoloader::writeAutoloadCache();
        $this->log(
            'File written : ' . Autoloader::CACHE_FILE,
            'Delete it to switch to classic autoload',
        );

        return 0;
    }
}