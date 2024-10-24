<?php

namespace YonisSavary\Sharp\Classes\CLI;

abstract class AbstractBuildTask extends CLIUtils
{
    /**
     * Main function of your build task, called every build
     * @return bool `true` if the task was successful, `false` if there was an error
     */
    public abstract function execute(): bool;
}