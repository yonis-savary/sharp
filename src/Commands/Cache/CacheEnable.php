<?php

namespace YonisSavary\Sharp\Commands\Cache;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;

class CacheEnable extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Enable every cache-able components !';
    }

    public function __invoke(Args $args)
    {
        $config = new Configuration(Configuration::DEFAULT_FILENAME);

        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists('cached', $configurable::getDefaultConfiguration()))
                continue;

            $this->log("Enabling [$key] cache");

            $config->edit($key, function($config){
                $config['cached'] = true;
                return $config;
            });
        }
        $config->save();
    }
}