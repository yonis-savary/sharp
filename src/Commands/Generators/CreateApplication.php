<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Utils;

class CreateApplication extends AbstractCommand
{
    public function createApplication(string $appName)
    {
        if (!preg_match("/^(\/?[A-Z][a-zA-Z0-9]*)+$/", $appName))
            return $this->log("Given app name must be made of PascalName words (can be separated by '/')");

        $appDirectory = Utils::relativePath($appName);

        if (is_dir($appDirectory))
            return $this->log("[$appName] already exists");

        $this->log("Making [$appName]");
        mkdir($appName, recursive:true);
    }

    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt('App name (PascalCase): ')];

        foreach($values as $app)
            $this->createApplication($app);

        $this->log('Enabling new applications');

        $config = new Configuration(Configuration::DEFAULT_FILENAME);
        $config->edit('applications', function($applications) use ($values) {
            array_push($applications, ...$values);
            return array_values(array_unique($applications));
        }, []);
        $config->save();
    }

    public function getHelp(): string
    {
        return 'Create an application directory and add it to your configuration';
    }
}