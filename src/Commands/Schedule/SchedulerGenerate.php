<?php

namespace YonisSavary\Sharp\Commands\Schedule;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;

class SchedulerGenerate extends AbstractCommand
{
    public function __invoke(Args $args)
    {
        $this->log(
            "Here's the CRON syntax to launch your app scheduler",
            '',
            '* * * * * cd '.Autoloader::projectRoot() .' && php do scheduler-launch',
            '',
        );
    }
}