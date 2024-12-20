<?php

namespace YonisSavary\Sharp\Commands\Schedule;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Core\Autoloader;

class SchedulerGenerate extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Generate a CRON command to launch the scheduler (use -c|--cron-only to only get the cron command)';
    }

    public function execute(Args $args): int
    {
        $command = '* * * * * cd '.Autoloader::projectRoot() .' && php do scheduler-launch';

        if ($args->isPresent("c", "cron-only"))
        {
            $this->log($command);
            return 0;
        }

        $this->log(
            "Here's the CRON syntax to launch your app scheduler",
            '',
            $command,
            '',
            "And here's a one-liner to add this job to your crontab",
            '',
            "{ crontab -l; echo \"$command\"; } | crontab -",
            ''
        );

        return 0;
    }
}