<?php

namespace YonisSavary\Sharp\Commands\Troubleshoot;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Commands\Troubleshoot\Contract\AbstractCodeChecker;
use YonisSavary\Sharp\Core\Autoloader;

class Troubleshoot extends Command
{
    public function getHelp(): string
    {
        return "Tries to find errors in framework/applications source code";
    }

    public function __invoke(Args $args)
    {
        $yellow = "\033[33;1m";
        $reset = "\033[0m";

        $autoloadCache = Autoloader::CACHE_FILE;
        if (is_file($autoloadCache))
            return print($yellow . "[$autoloadCache] file exists, please delete it and launch this command again\n" . $reset);

        /** @var array<AbstractCodeChecker> $checkers */
        $checkers = ObjectArray::fromArray(Autoloader::classesThatExtends(AbstractCodeChecker::class))
        ->map(fn($class) => new $class())
        ->collect();

        $configuration = Configuration::getInstance();
        $applications = $configuration->toArray("applications");
        $applications[] = "Sharp";

        foreach ($checkers as $checker)
        {
            echo $checker->getPurposeMessage() . "\n";

            foreach ($applications as $application)
            {
                $checker->checkApplication($application);
            }
            echo "\n";
        }
    }
}