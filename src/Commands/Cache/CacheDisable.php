<?php

namespace YonisSavary\Sharp\Commands\Cache;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;

class CacheDisable extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Disable every cache-able component (use -k to keep existants cache files)';
    }

    public function execute(Args $args): int
    {
        if (!$args->isPresent('-k', '--keep-files'))
        {
            $this->log('Clearing all cache files...');
            CacheClear::call('--all');
        }

        $config = new Configuration(Configuration::DEFAULT_FILENAME);

        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists('cached', $configurable::getDefaultConfiguration()))
                continue;

            $this->log("Disabling [$key] cache");

            $config->edit($key, function($config){
                $config['cached'] = false;
                return $config;
            });
        }
        $config->save();

        return 0;
    }
}