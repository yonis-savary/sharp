<?php

namespace YonisSavary\Sharp\Classes\CLI;

abstract class AbstractBuildTask extends CLIUtils
{
    /**
     * Main function of your build task, called every build
     * @return int 0 if the task was successful, a positive integer if there was an error
     */
    public abstract function execute(): int;

    /**
     * Return a list of directories/files that must be watched when using `build --watch`
     */
    public function getWatchList(): array
    {
        return [];
    }
}