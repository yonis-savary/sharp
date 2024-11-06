<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Core\Configurable;

class CreateConfiguration extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Create or Complete your configuration with the framework's default configuration";
    }

    public function execute(Args $args): int
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

            $this->log("Merging $configKey configuration...");
            foreach ($invalidKeys as $key)
                $this->log(" - Unsupported key [$key]");
        }

        $config->save();
        return 0;
    }
}