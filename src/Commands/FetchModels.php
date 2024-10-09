<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\ModelGenerator\ModelGenerator;

class FetchModels extends Command
{
    public function getHelp(): string
    {
        return "Create model classes from your database tables";
    }

    public function __invoke(Args $args)
    {
        $app = Terminal::chooseApplication();
        $generator = ModelGenerator::getInstance();
        $generator->generateAll($app);
    }
}