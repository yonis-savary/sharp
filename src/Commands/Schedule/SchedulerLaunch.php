<?php

namespace YonisSavary\Sharp\Commands\Schedule;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Extras\Scheduler;

class SchedulerLaunch extends AbstractCommand
{
    public function __invoke(Args $args)
    {
        Scheduler::getInstance()->executeAll();
    }

    public function getHelp(): string
    {
        return "Launch the different tasks of the scheduler (put this in your cron)";
    }
}