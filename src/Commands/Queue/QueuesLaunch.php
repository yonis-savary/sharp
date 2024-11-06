<?php

namespace YonisSavary\Sharp\Commands\Queue;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;

class QueuesLaunch extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Tell your applications queues to process one batch of items';
    }

    public function execute(Args $args): int
    {
        $this->log('Processing application queues');

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $this->log("$class...");
            $class::processQueue();
        }

        return 0;
    }
}