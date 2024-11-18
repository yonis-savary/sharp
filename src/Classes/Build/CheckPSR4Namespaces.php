<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CheckPSR4Namespaces extends AbstractBuildTask
{
    public function analyseDirectory(string $directory)
    {
        $composerFile = Utils::relativePath("composer.json", $directory);
        if (!is_file($composerFile))
        {
            $this->log("Composer file [$composerFile] not found !");
            return false;
        }

        $composer = new Configuration($composerFile);
        $map = $composer->get("autoload", [])["psr-4"] ?? [];

        $gotError = false;

        foreach ($map as $composerNamespace => $namespaceDirectory)
        {
            $namespaceDirectory = Utils::joinPath($directory, $namespaceDirectory);
            $files = Utils::exploreDirectory($namespaceDirectory, Utils::ONLY_FILES);

            foreach ($files as $file)
            {
                if (str_contains($file, "vendor/"))
                    continue;

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
                $gotError = true;
            }

        }

        if (!$gotError)
            $this->log("Everything is okay inside $directory");

        return $gotError;
    }

    public function execute(): int
    {
        if ($sharpRoot = $GLOBALS["sharp-src"] ?? null)
            $sharpRoot = realpath(Utils::joinPath($sharpRoot, ".."));

        $sharpRoot ??= Utils::relativePath("vendor/yonis-savary/sharp");

        return (int) ObjectArray::fromArray([
            Autoloader::projectRoot(),
            $sharpRoot
        ])
        ->unique()
        ->reduce(function(bool $acc, string $cur) {
            return $acc |= $this->analyseDirectory($cur);
        }, false);
    }

    public function getWatchList(): array
    {
        return ObjectArray::fromArray(Configuration::getInstance()->toArray("applications"))
            ->map(fn($x) => Utils::relativePath($x))
            ->collect();
    }
}