#!/bin/php
<?php

use YonisSavary\Sharp\Classes\CLI\Console;
use YonisSavary\Sharp\Core\Autoloader;

require_once 'vendor/autoload.php';

Autoloader::$ignoreRequireErrors = true;
Autoloader::initialize();

exit(Console::getInstance()->handleArgv($argv));