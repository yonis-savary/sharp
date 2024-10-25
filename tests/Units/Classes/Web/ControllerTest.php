<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Web;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Web\Controller;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

class ControllerTest extends TestCase
{
    public function test_declareRoutes()
    {
        $dummyControllerClass = new class {
            use Controller;

            public static function declareRoutes(Router $router)
            {
                $router->addRoutes(
                    Route::view("/login", "login-form"),
                    Route::post("/login", [self::class, "handleLogin"]),
                    Route::get ("/logout", [self::class, "handleLogout"]),
                );
            }
            public static function handleLogin(){}
            public static function handleLogout(){}
        };

        $router = new Router();
        $dummyControllerClass::declareRoutes($router);
        $this->assertCount(3, $router->getRoutes());
    }
}