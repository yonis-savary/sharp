<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

/**
 * This build task purpose is to install composer dependencies for every applications
 */
class InstallDependencies extends AbstractBuildTask
{
    public function execute(): bool
    {
        $this->log('Installing dependencies...');

        $applications = Autoloader::getLoadedApplications();

        foreach ($applications as $appName)
            $this->installDependenciesInApp($appName);

        return true;
    }

    protected function installDependenciesInApp(string $appName)
    {
        $appPath = $appName;

        if (!is_dir($appPath))
            $appPath = Utils::relativePath($appName);

        if (!is_dir($appPath))
            return $this->log("Cannot read [$appPath], inexistent directory");

        $relPathName = str_replace(Autoloader::projectRoot(), '', $appPath);
        $app = new Storage($appPath);

        if (!$app->isFile('composer.json'))
            return $this->log("Skipping [$relPathName] (no composer.json)");

        if ($app->isDirectory('vendor'))
            return $this->log("Skipping [$relPathName] (Already installed)");

        $this->log(
            "Installing in [$appName]",
            '---',
        );
        $this->shellInDirectory('composer install', $appPath);
        $this->log('---');
    }
}