<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CheckUseStatements extends AbstractBuildTask
{
    public function analyseFile(string $file)
    {
        $content = file_get_contents($file);
        $matches = [];
        preg_match_all("/^use (.+);$/mi", $content, $matches);
        $classnames = $matches[1];

        $gotError = false;

        foreach ($classnames as $classname)
        {
            if (class_exists($classname) || interface_exists($classname) || trait_exists($classname))
                continue;

            $gotError = true;
            $this->log("Use of inexistant class/interface/trait found");
            $this->log(" - [$classname] in $file");
        }

        return $gotError;
    }

    public function analyseDirectory(string $directory)
    {
        $composerFile = Utils::relativePath("composer.json", $directory);
        if (!is_file($composerFile))
        {
            $this->log("Skipping [$directory]");
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

                $gotError |= $this->analyseFile($file);
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