<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;

class EnableCaches extends Command
{
    public function getHelp(): string
    {
        return "Enable every cache-able components !";
    }

    public function __invoke(Args $args)
    {
        $config = Configuration::getInstance();
        foreach (Autoloader::classesThatUses(Configurable::class) as $configurable)
        {
            /** @var Configurable $configurable */
            $key = $configurable::getConfigurationKey();

            if (!array_key_exists("cached", $configurable::getDefaultConfiguration()))
                continue;

            echo "Enabling [$key] cache\n";

            $config->edit($key, function($config){
                $config["cached"] = true;
                return $config;
            });
        }
        $config->save();
    }
}