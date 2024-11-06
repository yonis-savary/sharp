<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CheckUnusedClasses extends AbstractBuildTask
{
    public function analyseFile(string $file)
    {
        $content = file_get_contents($file);
        $matches = [];
        preg_match_all("/^use.+?([\w]+);$/mi", $content, $matches);
        $usedClasses = $matches[1];

        $unused = [];

        foreach ($usedClasses as $classname)
        {
            $submatch = [];
            preg_match_all('/\b'. preg_quote($classname) .'\b/', $content, $submatch, PREG_OFFSET_CAPTURE);

            if (count($submatch[0]) > 1)
                continue;

            $unused[] = $classname;
        }

        if (count($unused))
        {
            $this->log($file);
            foreach ($unused as $class)
                $this->log(" - $class");
        }

        return count($unused) !== 0;
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
                $gotError |= $this->analyseFile($file);
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