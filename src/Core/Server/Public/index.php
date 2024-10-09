<?php

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Router;

require_once "../vendor/autoload.php";

$request = Request::buildFromGlobals();
$request->logSelf();

$router = Router::getInstance();

$response = $router->route($request);
$response->logSelf();
$response->display();

