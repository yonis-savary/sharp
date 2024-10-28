<?php

use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

Router::getInstance()->addRoutes(
    Route::get("/utils/sleep", function(){
        usleep(500_000);
        return "ok";
    }),

    Route::get("/utils/error", function(){
        throw new Exception("This is a test exception");
    })
);