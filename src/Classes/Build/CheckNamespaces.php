<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CheckNamespaces extends AbstractBuildTask
{
    public function analyseDirectory(string $directory)
    {
        $composerFile = Utils::relativePath("composer.json", $directory);
        if (!is_file($composerFile))
        {
            $this->log("Composer file [$composerFile] not found !");
            return true;
        }

        $composer = new Configuration($composerFile);
        $map = $composer->get("autoload", [])["psr-4"] ?? [];

        $noError = true;

        foreach ($map as $composerNamespace => $namespaceDirectory)
        {
            $namespaceDirectory = Utils::joinPath($directory, $namespaceDirectory);
            $files = Utils::exploreDirectory($namespaceDirectory, Utils::ONLY_FILES);

            foreach ($files as $file)
            {
                $content = file_get_contents($file);
                $matches = [];
                if (!preg_match("/^namespace (.+);/m", $content, $matches))
                    continue;

                $fileNamespace = $matches[1];

                $expectedNamespace = str_replace($namespaceDirectory, $composerNamespace, dirname($file));
                $expectedNamespace = Utils::pathToNamespace($expectedNamespace);

                if ($fileNamespace === $expectedNamespace)
                    continue;

                $this->log(
                    "Invalid namespace in $file ! ",
                    " - Found    : $fileNamespace",
                    " - Expected : $expectedNamespace"
                );
                $noError = false;
            }

        }

        if ($noError)
            $this->log("Everything is okay inside $directory");

        return $noError;
    }

    public function execute(): bool
    {
        if ($sharpRoot = $GLOBALS["sharp-src"] ?? null)
            $sharpRoot = realpath(Utils::joinPath($sharpRoot, ".."));

        $sharpRoot ??= Utils::relativePath("vendor/yonis-savary/sharp");

        return ObjectArray::fromArray([
            Autoloader::projectRoot(),
            $sharpRoot
        ])
        ->unique()
        ->reduce(function(bool $acc, string $cur) {
            return $acc &= $this->analyseDirectory($cur);
        }, true);
    }
}