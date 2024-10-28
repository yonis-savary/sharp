<?php

use YonisSavary\Sharp\Classes\Core\Context;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Core\Autoloader;

require_once '../vendor/autoload.php';

Autoloader::initialize();

$request = Request::fromGlobals();
$request->logSelf();
Context::set($request);

$router = Router::getInstance();

$response = $router->route($request);
$response->logSelf();
$response->display();

