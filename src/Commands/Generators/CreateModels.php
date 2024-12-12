<?php

namespace YonisSavary\Sharp\Commands\Generators;

use Throwable;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\ModelGenerator\ModelGenerator;
use YonisSavary\Sharp\Core\Autoloader;

class CreateModels extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Create model classes from your database tables';
    }

    public function execute(Args $args): int
    {
        if ($args->isPresent("c", "choose"))
            $app = Terminal::chooseApplication();
        else
            $app = $this->getMainApplicationPath();

        $generator = ModelGenerator::getInstance();
        try
        {
            $generator->generateAll($app);
            $appRelPath = str_replace(Autoloader::projectRoot()."/", "", $app);
            $this->log($this->withGreenColor("Models fetched into [./$appRelPath] !"));
            return 0;
        }
        catch(Throwable $err)
        {
            error($err);
            $this->log($this->withRedColor("Caught an error while fetching models ! Please see your logs"));
            return 1;
        }
    }
}