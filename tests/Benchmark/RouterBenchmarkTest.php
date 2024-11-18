<?php

namespace YonisSavary\Sharp\Tests\Benchmark;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

class RouterBenchmarkTest extends TestCase
{
    public static function sayHello(): string
    {
        return "Hello";
    }

    public function test_cachingTime()
    {
        $cache = new Cache(Storage::getInstance()->getSubStorage(uniqid("router-cache")));
        $config = Configuration::fromArray(["router" => ["cached" => true]]);

        $router = new Router($cache, $config);

        for ($i=0; $i<=50_000; $i++)
            $router->addRoutes(Route::get("/$i", [self::class, "sayHello"]));

        $start = hrtime(true);
        $router->route(new Request("GET", "/50000"));
        $firstTime = (hrtime(true) - $start) / 1000000;
        $this->assertLessThan(100, $firstTime);

        $start = hrtime(true);
        $router->route(new Request("GET", "/50000"));
        $secondTime = (hrtime(true) - $start) / 1000000;
        $this->assertLessThan(3, $secondTime);
    }
}