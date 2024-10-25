<?php

use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

Router::getInstance()->addRoutes(
    Route::get("/utils/sleep", function(){
        usleep(500_000);
        return "ok";
    })
);