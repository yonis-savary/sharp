<?php

use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

define('ROUTE_OBJECT_IN_ROUTE_APP_FILE', $router);


Router::getInstance()->addRoutes(

    Route::redirect("/", "/root"),
    Route::get('/root', fn() => 'Hello!'),
    Route::post('/double/{int:id}', fn($_, int $id) => $id * 2),

    Route::get("/error", fn() => throw new Exception("Dummy exception !"))

);