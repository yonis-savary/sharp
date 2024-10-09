<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Utils;

class Test extends Command
{
    public function getHelp(): string
    {
        return "Execute every PHPUnit installation/test suites";
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
        $toTest = Configuration::getInstance()->toArray("applications");

        // The framework need to be tested too
        array_unshift($toTest, "vendor/yonis-savary/sharp");

        foreach ($toTest as $application)
        {
            $phpunit = Utils::joinPath($application, "vendor/bin/phpunit");
            if (!is_file($phpunit))
                continue;

            $this->executeInDir(function() use ($application) {

                $start = hrtime(true);

                $command = "./vendor/bin/phpunit";
                if (str_starts_with(PHP_OS, "WIN"))
                    $command = ".\\vendor\\bin\\phpunit";

                $output = shell_exec("$command --colors=never --display-warnings") ?? "";
                $duration = hrtime(true) - $start;

                $durationMilliseconds = $duration/1_000_000;

                $lines = array_filter(explode("\n", $output));

                $lastLine = end($lines);

                if (str_starts_with($lastLine, "OK"))
                    echo " - OK ($application, " . substr($lastLine, 4) ." in $durationMilliseconds ms\n";
                else
                    echo "Errors/Warnings while testing [$application] :\n$output";

            }, $application);
        }
    }
}