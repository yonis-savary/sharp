<?php

namespace YonisSavary\Sharp\Commands\Queue;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;
use YonisSavary\Sharp\Core\Autoloader;

class QueuesClear extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Delete every files from your application queues';
    }

    public function execute(Args $args): int
    {
        if (!Terminal::confirm('This action will delete every queue item in your application, process ?'))
            return 0;

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $storage = $class::getQueueStorage();

            $this->progressBar($storage->listFiles(), function($file){
                $this->log("Deleting $file");
                unlink($file);
            });
        }

        return 0;
    }
}