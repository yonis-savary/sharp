<?php

namespace YonisSavary\Sharp\Commands;

use Throwable;
use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class Build extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Call every AbstractBuildTask classes in your application';
    }

    public function launchAllTasks()
    {
        $this->log('Building application...');
        $logger = new Logger("build.csv");

        $buildClasses = Autoloader::classesThatExtends(AbstractBuildTask::class);

        $gotAnyError = false;

        $this->progressBar($buildClasses, function($class) use (&$logger, &$gotAnyError) {

            $logger->info("---[{class}]---", ["class" => $class]);

            ob_start();

            try
            {
                $task = new $class();
                $gotError = $task->execute();
                $output = ob_get_clean();
            }
            catch (Throwable $thrown)
            {
                while (ob_get_level())
                    ob_get_clean();

                $gotError = true;
                $output = "Got an error while building !\n" . $thrown->getMessage();
                $logger->warning("Caught an exception while launching $class");
                $logger->warning($thrown);
            }

            if ($gotError)
            {
                $gotAnyError = true;
                $this->log($this->withRedColor("✗") . " " . $class);
                $this->log($output);
            }
            else
            {
                $this->log($this->withGreenColor("✓") . " " . $class);
            }

            if (trim($output))
                $logger->info($output);
        });

        $gotAnyError |= Test::execute();

        return (int) $gotAnyError;
    }





    public function startToWatch()
    {
        set_time_limit(0);

        $buildClasses = Autoloader::classesThatExtends(AbstractBuildTask::class);
        foreach ($buildClasses as &$class)
            $class = new $class();
        /** @var array<AbstractBuildTask> $buildClasses */

        $directoryWatchList = [];
        $fileWatchList = [];

        $this->log("Building watch list...");

        foreach ($buildClasses as $task)
        {
            $taskList = $task->getWatchList();

            foreach ($taskList as $dirOrFile)
            {
                if (!file_exists($dirOrFile))
                {
                    $this->log($this->withYellowColor("Skipping inexistant directory or file [$dirOrFile]"));
                    continue;
                }

                $array = &$fileWatchList;
                if (is_dir($dirOrFile))
                    $array = &$directoryWatchList;

                $array[$dirOrFile] ??= [];
                $array[$dirOrFile][] = $task::class;
            }
        }

        $this->log("Watching ". count($directoryWatchList) ." directories and ". count($fileWatchList) ." files for changes");
        $this->log("Type Ctrl+C to interrupt", "");

        $diggestMemory = [];

        while (true)
        {
            $fileToProcess = $fileWatchList;
            $taskToLaunch = [];

            foreach ($directoryWatchList as $directory => $tasks)
            {
                foreach (Utils::exploreDirectory($directory, Utils::ONLY_FILES) as $file)
                {
                    $fileToProcess[$file] ??= [];
                    array_push($fileToProcess[$file], ...$tasks);
                }
            }

            $changeCount = 0;
            foreach ($fileToProcess as $file => $tasks)
            {
                $newMd5 = md5_file($file);
                $oldMd5 = $diggestMemory[$file] ?? false;

                if ($oldMd5 != $newMd5)
                {
                    $changeCount++;
                    $diggestMemory[$file] = $newMd5;
                    array_push($taskToLaunch, ...$tasks);
                }
            }

            if ($changeCount)
                $this->log("Found changes in $changeCount files");

            $taskToLaunch = array_unique($taskToLaunch);


            $this->progressBar($taskToLaunch, function($class) {
                /** @var AbstractBuildTask $task */
                $task = new $class;

                ob_start();
                try
                {
                    $error = $task->execute();
                    $output = ob_get_clean();

                    if ($error)
                    {
                        $this->log($this->withRedColor("✗") . " " . $class);
                        $this->log("Got error(s) ($error) with $class, see your logs for more");
                        $this->log($output);
                    }
                    else
                    {
                        $this->log($this->withGreenColor("✓") . " " . $class);
                    }
                }
                catch (Throwable $err)
                {
                    ob_get_clean();
                    $this->log($this->withRedColor("✗") . " " . $class);
                    $this->log("Caught an exception while launching $class, see your logs for more");
                    Logger::getInstance()->error($err);
                }
            });
            sleep(3);
        }
    }





    public function __invoke(Args $args)
    {
        return $args->isPresent("w", "watch") ?
            $this->startToWatch():
            $this->launchAllTasks();
    }
}