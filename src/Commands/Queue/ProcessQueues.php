<?php

namespace YonisSavary\Sharp\Commands\Queue;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Command;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;

class ProcessQueues extends Command
{
    public function getHelp(): string
    {
        return "Tell your applications queues to process one batch of items";
    }

    public function __invoke(Args $args)
    {
        $this->log("Processing application queues");

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $this->log("$class...");
            $class::processQueue();
        }
    }
}