<?php

use YonisSavary\Sharp\Classes\Core\Context;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Events\LoadedFramework;
use YonisSavary\Sharp\Classes\Events\LoadingFramework;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Router;

require_once "../vendor/autoload.php";

EventListener::getInstance()->dispatch(new LoadingFramework());
EventListener::getInstance()->dispatch(new LoadedFramework());

$request = Request::buildFromGlobals();
$request->logSelf();
Context::set($request);

$router = Router::getInstance();

$response = $router->route($request);
$response->logSelf();
$response->display();

