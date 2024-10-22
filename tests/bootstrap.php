<?php

use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\ModelGenerator\ModelGenerator;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Test\SharpServer;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

$GLOBALS["sharp-root"] = realpath(__DIR__);
$GLOBALS["sharp-src"] = realpath(__DIR__ . "/../src");

require_once __DIR__ . "/../vendor/autoload.php";

/*

This script purpose is to be an alternative to /Sharp/bootstrap.php

The goal is to make a good envrionment to Test (with Database, Configuration...etc)
------------------------------------------------

*/

EventListener::removeInstance();

$testLogger = new Logger("test-suite.csv", new Storage(__DIR__));
Logger::setInstance($testLogger);

$testLogger->info("Starting test suite");
$testLogger->info("Sharp root directory : {dir}", ["dir" => $GLOBALS["sharp-root"]]);
$testLogger->info("Sharp src directory : {dir}", ["dir" => $GLOBALS["sharp-src"]]);

$testStoragePath = __DIR__ . "/TestApp" ;
Autoloader::loadApplication($testStoragePath);

$testStorage = new Storage(Utils::relativePath( __DIR__ ."/Storage"));
Storage::setInstance($testStorage);

$testConfig = new Configuration(Utils::relativePath(__DIR__ . "/sharp.json"));
Configuration::getInstance()->merge($testConfig->dump());
Cache::setInstance(new Cache($testStorage, "Cache"));

resetTestDatabase();

$generator = ModelGenerator::getInstance();
$generator->generateAll(Utils::relativePath($testStoragePath), 'YonisSavary\\Sharp\\Tests\\TestApp\\Models');


register_shutdown_function(function(){
    if (!SharpServer::hasInstance())
        return;

    $server = SharpServer::getInstance();

    $logger = Logger::getInstance();
    $logger->info($server->getOutput());
    $logger->info($server->getErrorOutput());
});