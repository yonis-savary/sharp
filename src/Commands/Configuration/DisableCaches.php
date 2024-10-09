<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Commands\ClearCaches;

class DisableCaches extends Command
{
    public function getHelp(): string
    {
        return "Disable every cache-able component (use -k to keep existants cache files)";
    }

    public function __invoke(Args $args)
    {
        if (!$args->isPresent("-k", "--keep-files"))
        {
            echo "Clearing all cache files...";
            ClearCaches::execute("--all");
        }

        $config = new Configuration(Configuration::DEFAULT_FILENAME);

        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists("cached", $configurable::getDefaultConfiguration()))
                continue;

            echo "Disabling [$key] cache\n";

            $config->edit($key, function($config){
                $config["cached"] = false;
                return $config;
            });
        }
        $config->save();
    }
}