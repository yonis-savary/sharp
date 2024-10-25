<?php

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

Router::getInstance()->addRoutes(
    Route::get("/math/double/{int:number}", fn($_, int $n) => $n*2),
    Route::post("/math/multiply", function(Request $request) {
        list($a, $b) = $request->list("a", "b");
        return (int)$a * (int)$b;
    })
);