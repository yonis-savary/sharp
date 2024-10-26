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

        return count($unused) === 0;
    }

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
                $noError &= $this->analyseFile($file);
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