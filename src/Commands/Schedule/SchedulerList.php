<?php

namespace YonisSavary\Sharp\Commands\Schedule;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Extras\Scheduler;
use YonisSavary\Sharp\Classes\Extras\SchedulerHandler;

class SchedulerList extends AbstractCommand
{
    public function execute(Args $args): int
    {
        $handlers = Scheduler::getInstance()->listAll();
        $this->log('List of scheduler handlers');

        ObjectArray::fromArray(array_values($handlers))
        ->forEach(function(SchedulerHandler $handler) {

            $description = $handler->toSentence();

            $description = trim(preg_replace_callback("/( ?every \w+(,|$)){2,}/", function($match) use ($description) {
                $submatch = explode(',', $match[0])[0];

                return $submatch . ',';
            }, $description, 1));


            $description = preg_replace("/,$/", '', $description);
            if (!$description) $description = 'every minute';

            $description = ucfirst($description);

            $this->log(' - ' . $handler->identifier . " : $description");
        });

        return 0;
    }

    public function getHelp(): string
    {
        return 'List every task registered in the scheduler';
    }
}