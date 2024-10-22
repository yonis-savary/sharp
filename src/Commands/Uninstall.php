<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class Uninstall extends AbstractCommand
{
    public function __invoke(Args $args)
    {
        $projectRoot = Autoloader::projectRoot();
        $this->shellInDirectory("git clean -dfXn", $projectRoot);

        if (Terminal::confirm("Git : Delete every untracked files ? This action cannot be undone"))
            $this->shellInDirectory("git clean -dfX", $projectRoot);

        $this->log("Uninstalling dependencies...");

        $applications = Configuration::getInstance()->toArray("applications");

        foreach ($applications as $appName)
            $this->uninstallAppVendor($appName);
    }

    public function getHelp(): string
    {
        return "Delete every ignored files and vendor directories (Preview before deletion for ignored files)";
    }

    protected function recursiveDeleteDirectory(Storage $rootDirectory)
    {
        $subFiles = $rootDirectory->exploreDirectory("/", Utils::ONLY_FILES);
        foreach (array_reverse($subFiles) as $file)
            unlink($file);
        $this->log(" - Deleted " . count($subFiles) . " files");

        $subDirectories = $rootDirectory->exploreDirectory("/", Utils::ONLY_DIRS);
        foreach (array_reverse($subDirectories) as $directory)
            rmdir($directory);
        $this->log(" - Deleted " . count($subDirectories) . " directories");

        rmdir($rootDirectory->getRoot());
    }

    protected function uninstallAppVendor(string $appName)
    {
        $appPath = Utils::relativePath($appName);
        $app = new Storage($appPath);

        if (!$app->isDirectory("vendor"))
            return $this->log("No vendor directory in " . $appName);

        $this->log("Uninstalling vendor in ". $app->getRoot());

        $vendorDir = $app->getSubStorage("vendor");
        $this->recursiveDeleteDirectory($vendorDir);
    }
}