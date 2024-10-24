<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Utils;

class Test extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Execute every PHPUnit installation/test suites';
    }

    protected function executeInDir(callable $callback, string $directory)
    {
        $original = getcwd();
        chdir($directory);
        $callback();
        chdir($original);
    }

    public function __invoke(Args $args)
    {
        $toTest = Configuration::getInstance()->toArray('applications');

        array_unshift($toTest, '.');

        foreach ($toTest as $application)
        {
            $phpunit = Utils::joinPath($application, 'vendor/bin/phpunit');
            if (!is_file($phpunit))
                continue;

            $this->executeInDir(function() use ($application) {

                $start = hrtime(true);

                $command = './vendor/bin/phpunit';
                if (str_starts_with(PHP_OS, 'WIN'))
                    $command = ".\\vendor\\bin\\phpunit";

                $output = shell_exec("$command --colors=never --display-warnings") ?? '';
                $duration = hrtime(true) - $start;

                $durationMilliseconds = $duration/1_000_000;

                $lines = array_filter(explode("\n", $output));

                $lastLine = end($lines);

                if (str_starts_with($lastLine, 'OK'))
                    $this->log(" - OK ($application, " . substr($lastLine, 4) ." in $durationMilliseconds ms");
                else
                    $this->log("Errors/Warnings while testing [$application] :", $output);

            }, $application);
        }
    }
}