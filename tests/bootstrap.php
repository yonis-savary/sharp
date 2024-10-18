<?php

use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelGenerator\ModelGenerator;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

$GLOBALS["sharp-root"] = realpath(".");
$GLOBALS["sharp-src"] = realpath( __DIR__ . "/../src");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/bootstrap.php";

/*

This script purpose is to be an alternative to /Sharp/bootstrap.php

The goal is to make a good envrionment to Test (with Database, Configuration...etc)
------------------------------------------------

*/

EventListener::removeInstance();

$defaultStorage = Storage::getInstance();
Logger::setInstance(new Logger("test-suite.csv", $defaultStorage->getSubStorage("Logs")));

$testStoragePath = __DIR__ ;
Autoloader::loadApplication($testStoragePath);

$testStorage = new Storage(Utils::relativePath("$testStoragePath/tmp_test_storage"));
Storage::setInstance($testStorage);

$testConfig = new Configuration(Utils::relativePath("$testStoragePath/config.json"));
Configuration::getInstance()->merge($testConfig->dump());
Cache::setInstance(new Cache($testStorage, "Cache"));

resetTestDatabase();

$generator = ModelGenerator::getInstance();
$generator->generateAll(Utils::relativePath($testStoragePath), 'YonisSavary\\Sharp\\Tests\\Models');

/**
 * Remove every files in the Test Storage before deleting the directory
 */
register_shutdown_function(function () use (&$testStorage){

    $files = array_reverse($testStorage->exploreDirectory(mode: Storage::ONLY_FILES));
    $dirs = array_reverse($testStorage->exploreDirectory(mode: Storage::ONLY_DIRS));

    foreach ($files as $file)
        $testStorage->unlink($file);

    foreach ($dirs as $directory)
        $testStorage->removeDirectory($directory);

    $testStorage->removeDirectory($testStorage->getRoot());
});