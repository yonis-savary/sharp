<?php

namespace YonisSavary\Sharp\Commands\Configuration;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Commands\Build;

class EnableApplication extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Enable applications by putting them in your configuration';
    }

    public function __invoke(Args $args)
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt('App to enable (PascalCase): ')];

        $values = ObjectArray::fromArray($values);
        $values = $values->filter(function($app) {
            if (is_dir($app))
                return true;

            $this->log("Skipping, [$app] is not a directory)");
            return false;
        })->collect();

        $this->log('Enabling new applications');

        $config = new Configuration(Configuration::DEFAULT_FILENAME);

        $config->edit('applications', function($applications) use ($values) {
            return ObjectArray::fromArray($applications)
            ->push(...$values)
            ->unique()
            ->collect();
        }, []);

        $config->save();

        $build = new Build();
        $build(new Args());
    }
}