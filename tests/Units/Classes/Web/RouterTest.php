<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Web;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;

class RouterTest extends TestCase
{
    public static function sampleCallbackA() { return Response::json('A'); }
    public static function sampleCallbackB() { return Response::json('B'); }

    public function test_createGroup()
    {
        $router = new Router();

        $myGroup = $router->createGroup("api", "A", ["my-prop" => "my-value"]);

        $this->assertEquals([
            "path" => ["api"],
            "middlewares" => ["A"],
            "extras" => ["my-prop" => "my-value"]
        ], $myGroup);
    }

    public function test_group()
    {
        $dummyMiddleware = new class implements MiddlewareInterface  {public static function handle(Request $request): Request|Response { return $request; } };

        $router = new Router();

        $assertRoutesAreGrouped = function(Router $router) use ($dummyMiddleware) {
            $this->assertCount(2, $router->getRoutes());
            foreach ($router->getRoutes() as $route)
            {
                $this->assertStringStartsWith('/api', $route->getPath());
                $this->assertEquals([$dummyMiddleware], $route->getMiddlewares());
            }
        };

        $group = [
            'path' => 'api',
            'middlewares' => $dummyMiddleware
        ];

        $router = new Router();
        $router->groupCallback($group, function(Router $router){
            $router->addRoutes(
                Route::view('/about', 'about'),
                Route::view('/contact', 'contact')
            );
        });
        $assertRoutesAreGrouped($router);

        $router = new Router();
        $router->addRoutes(
            ...$router->group(
                $group,
                Route::view('/about', 'about'),
                Route::view('/contact', 'contact')
            )
        );
        $assertRoutesAreGrouped($router);

        $router = new Router();
        $router->addGroup(
            $group,
            Route::view('/about', 'about'),
            Route::view('/contact', 'contact')
        );
        $assertRoutesAreGrouped($router);
    }

    public function test_route()
    {
        $r = new Router();

        $dummy = null;

        $r->addRoutes(
            Route::get('/', function() use (&$dummy) { $dummy='A'; }, []),
            Route::post('/', function() use (&$dummy) { $dummy='B'; }, []),
            Route::get('/home', fn() => Response::json('OK'), []),

            Route::get('/{int:n}', function($_, int $n) use (&$dummy) { $dummy=$n; }, []),
            Route::get('/slug/{int:n}', function(Request $req) use (&$dummy) { $dummy = $req->getSlugs()['n']; }, []),
            Route::get('/slug-name/{int:n}', function(Request $req) use (&$dummy) { $dummy = $req->getSlug('n'); }, []),
        );

        $r->route(new Request('GET', '/'));
        $this->assertEquals('A', $dummy);

        $r->route(new Request('POST', '/'));
        $this->assertEquals('B', $dummy);

        $res = $r->route(new Request('GET', '/home'));
        $this->assertInstanceOf(Response::class, $res);

        $r->route(new Request('GET', '/1'));
        $this->assertEquals(1, $dummy);

        $r->route(new Request('GET', '/2'));
        $this->assertEquals(2, $dummy);

        $r->route(new Request('GET', '/slug/3'));
        $this->assertEquals(3, $dummy);

        $r->route(new Request('GET', '/slug-name/4'));
        $this->assertEquals(4, $dummy);
    }



    public function test_getRoutes()
    {
        $router = new Router();

        $router->addRoutes(
            Route::get("/", fn() => null),
            Route::get("/a", fn() => null),
        );

        $this->assertCount(2, $router->getRoutes());
    }




    /**
     * Test if the Router cache correctly routes that have the same path but
     * support different methods
     */
    public function test_issue_cached_same_path_different_methods()
    {
        $cache = new Cache(Storage::getInstance()->getSubStorage('test_router_issue_1'));
        $cache->deleteAll();

        $router = new Router($cache);
        $router->setConfiguration(['cached' => true]);

        $this->assertTrue($router->isCached());

        $router->addRoutes(
            Route::get('/', [self::class, 'sampleCallbackA']),
            Route::post('/', [self::class, 'sampleCallbackB'])
        );

        $res = $router->route( new Request('GET', '/') );
        $this->assertEquals('A', $res->getContent());
        $this->assertCount(1, $cache->getKeys());

        $res = $router->route( new Request('POST', '/') );
        $this->assertEquals('B', $res->getContent());
        $this->assertCount(2, $cache->getKeys());

    }

    public function test_issue_same_path_with_suffix()
    {
        $router = new Router();

        $dummy = 0;

        $router->addRoutes(
            Route::get('/{id}', function($_, int $id) use (&$dummy) { $dummy = $id; }),
            Route::get('/{id}/suffix', fn() => null  /* Do nothing !*/)
        );

        $router->route(new Request('GET', '/5'));
        $this->assertEquals(5, $dummy);

        $router->route(new Request('GET', '/10'));
        $this->assertEquals(10, $dummy);

        $router->route(new Request('GET', '/9999/suffix'));
        $this->assertEquals(10, $dummy);
    }

}