<?php

namespace YonisSavary\Sharp\Commands\Queue;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;
use YonisSavary\Sharp\Core\Autoloader;

class QueuesClear extends Command
{
    public function getHelp(): string
    {
        return "Delete every files from your application queues";
    }

    public function __invoke(Args $args)
    {
        if (!Terminal::confirm("This action will delete every queue item in your application, process ?"))
            return;

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $storage = $class::getQueueStorage();

            $this->progressBar($storage->listFiles(), function($file){
                $this->log("Deleting $file");
                unlink($file);
            });
        }
    }
}