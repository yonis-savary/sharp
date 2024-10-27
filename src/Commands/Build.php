<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Autoloader;

class Build extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Call every AbstractBuildTask classes in your application';
    }

    public function __invoke(Args $args)
    {
        $this->log('Building application...');
        $logger = new Logger("build.csv");

        $buildClasses = Autoloader::classesThatExtends(AbstractBuildTask::class);
        $this->progressBar($buildClasses, function($class) use (&$logger) {

            $logger->info("---[{class}]---", ["class" => $class]);

            ob_start();
            $task = new $class();
            $successful = $task->execute();
            $output = ob_get_clean();

            if ($successful)
            {
                $this->log($this->withGreenColor("✓") . " " . $class);
            }
            else
            {
                $this->log($this->withRedColor("✗") . " " . $class);
                $this->log($output);
            }

            if (trim($output))
                $logger->info($output);
        });

        Test::execute();
    }
}