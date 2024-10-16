<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;

class CreateConfiguration extends Command
{
    public function getHelp(): string
    {
        return "Create or Complete your configuration with the framework's default configuration";
    }

    public function __invoke(Args $args)
    {
        $configurableList = Autoloader::classesThatUses(Configurable::class);
        $config = new Configuration(Configuration::DEFAULT_FILENAME);

        /**
         * @var Configurable $class
         */
        foreach ($configurableList as $class)
        {
            $configKey = $class::getConfigurationKey();

            $actual = $config->get($configKey, []);
            $default = $class::getDefaultConfiguration();

            $config->set($configKey, array_merge($default, $actual));

            $invalidKeys = array_diff(array_keys($actual), array_keys($default));

            echo "Merging $configKey configuration...\n";
            foreach ($invalidKeys as $key)
                echo " - Unsupported key [$key]\n";
        }

        $config->save();
    }
}