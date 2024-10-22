<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;

class Build extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Call every AbstractBuildTask classes in your application";
    }

    public function __invoke(Args $args)
    {
        $this->log("Building app...");

        /** @var AbstractBuildTask $class */
        foreach (Autoloader::classesThatExtends(AbstractBuildTask::class) as $class)
        {
            $this->log(sprintf("Executing [%s]\n", $class));

            $task = new $class();
            $task->execute();

            $this->log("");
        }
    }
}