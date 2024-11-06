<?php

namespace YonisSavary\Sharp\Commands\Schedule;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Extras\Scheduler;

class SchedulerLaunch extends AbstractCommand
{
    public function execute(Args $args): int
    {
        Scheduler::getInstance()->executeAll();
        return 0;
    }

    public function getHelp(): string
    {
        return 'Launch the different tasks of the scheduler (put this in your cron)';
    }
}