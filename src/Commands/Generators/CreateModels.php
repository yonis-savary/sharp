<?php

namespace YonisSavary\Sharp\Commands\Generators;

use Throwable;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\ModelGenerator\ModelGenerator;

class CreateModels extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Create model classes from your database tables';
    }

    public function __invoke(Args $args)
    {
        if ($args->isPresent("c", "choose"))
            $app = Terminal::chooseApplication();
        else
            $app = $this->getMainApplicationPath();

        $generator = ModelGenerator::getInstance();
        try
        {
            $generator->generateAll($app);
            $this->log($this->withGreenColor("Models fetched into [$app] !"));
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