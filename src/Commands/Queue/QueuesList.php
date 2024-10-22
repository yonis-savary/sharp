<?php

namespace YonisSavary\Sharp\Commands\Queue;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Classes\Extras\QueueHandler;

class QueuesList extends AbstractCommand
{
    public function getHelp(): string
    {
        return "List items from your application queues, use --list to get a full list";
    }

    public function __invoke(Args $args)
    {
        $list = $args->isPresent("-l", "--list");

        $this->log("Listing application queues\n");

        /** @var QueueHandler $class */
        foreach (Autoloader::classesThatUses(QueueHandler::class) as $class)
        {
            $storage = $class::getQueueStorage();
            $files = $storage->listFiles();
            $this->log(sprintf("%s (%s items)", $class, count($files)));

            if (!$list)
                continue;

            foreach ($files as $file)
                $this->log(" - $file");
        }

    }
}