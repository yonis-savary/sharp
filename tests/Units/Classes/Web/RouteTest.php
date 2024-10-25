<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Web;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Core\Utils;

class RouteTest extends TestCase
{
    public function test_any()
    {
        $route = Route::any('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals([], $route->getMethods());
    }

    public function test_get()
    {
        $route = Route::get('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['GET'], $route->getMethods());
    }

    public function test_post()
    {
        $route = Route::post('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['POST'], $route->getMethods());
    }

    public function test_patch()
    {
        $route = Route::patch('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['PATCH'], $route->getMethods());
    }

    public function test_put()
    {
        $route = Route::put('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['PUT'], $route->getMethods());
    }

    public function test_delete()
    {
        $route = Route::delete('/', fn()=>'A');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals(['DELETE'], $route->getMethods());
    }

    public function test_view()
    {
        $route = Route::view('/', "sharp-tests-child", context:["variable" => "VARIABLE"]);
        $this->assertInstanceOf(Route::class, $route);

        $response = $route(new Request("GET", "/"));
        $content = $response->getContent();

        $this->assertTrue(substr_count($content, 'CHILD') == 1);
        $this->assertTrue(substr_count($content, 'PARENT') == 1);
        $this->assertTrue(substr_count($content, 'COMPONENT') == 2);
        $this->assertTrue(substr_count($content, 'VARIABLE') == 1);

    }

    public function test_file()
    {
        $relPath = 'TestApp/Classes/Animals/Duck.php';
        $absPath = Utils::relativePath($relPath);

        $route = Route::file('/my-file', $relPath);
        $req = new Request('GET', '/my-file');

        /** @var Response $response */
        $response = $route($req);

        $this->assertEquals($absPath, $response->getContent());

        ob_start();
        $response->display(false);
        $displayed = ob_get_clean();

        $this->assertStringContainsString('class Duck', $displayed);
    }

    public function test_redirect()
    {
        $route = Route::redirect('/', "/another");
        $this->assertInstanceOf(Route::class, $route);

        /** @var Response $response */
        $response = $route(new Request("GET", "/"));

        $this->assertEquals("/another", $response->getHeader("location"));
    }

    public function test_getSetPath()
    {
        $myPath = '/A';
        $secondPath = '/B';
        $route = new Route($myPath, fn()=>null);

        $this->assertEquals($myPath, $route->getPath());
        $route->setPath($secondPath);
        $this->assertEquals($secondPath, $route->getPath());
    }

    public function test_getSetCallback()
    {
        $myCallback = fn()=>'A';
        $secondCallback = fn()=>'B';
        $route = new Route('/', $myCallback);

        $this->assertEquals($myCallback, $route->getCallback());
        $route->setCallback($secondCallback);
        $this->assertEquals($secondCallback, $route->getCallback());
    }

    public function test_getSetMethods()
    {
        $myMethods = ['A'];
        $secondMethods = ['B'];
        $route = new Route('/', fn()=>null, $myMethods);

        $this->assertEquals($myMethods, $route->getMethods());
        $route->setMethods($secondMethods);
        $this->assertEquals($secondMethods, $route->getMethods());
    }

    public function test_getSetMiddlewares()
    {
        $firstMiddleware  = new class implements MiddlewareInterface { public static function handle(Request $request): Request|Response { return $request; } };
        $secondMiddleware = new class implements MiddlewareInterface { public static function handle(Request $request): Request|Response { return $request; } };

        $route = new Route('/', fn()=>null, [], [$firstMiddleware]);

        $this->assertEquals([$firstMiddleware], $route->getMiddlewares());
        $route->setMiddlewares([$secondMiddleware]);
        $this->assertEquals([$secondMiddleware], $route->getMiddlewares());


        $route = new Route('/', fn()=>null, [], [$firstMiddleware]);

        $this->assertEquals([$firstMiddleware], $route->getMiddlewares());
        $route->addMiddlewares($secondMiddleware);
        $this->assertEquals([$firstMiddleware, $secondMiddleware], $route->getMiddlewares());
    }

    public function test_getSetExtras()
    {
        $myExtras = ['A'];
        $secondExtras = ['B'];
        $route = new Route('/', fn()=>null, ['GET'], [], $myExtras);

        $this->assertEquals($myExtras, $route->getExtras());
        $route->setExtras($secondExtras);
        $this->assertEquals($secondExtras, $route->getExtras());
    }

    public function test___invoke()
    {
        $dummyRequest = new Request('GET', '/');
        $route = new Route('/', fn()=> new Response(5, 200));

        $res = $route($dummyRequest);
        $this->assertEquals(5, $res->getContent());
    }

    public function test_match()
    {
        $dummyCallback = fn()=>false;

        // Any path - any method
        $anyRoute = new Route('/', $dummyCallback);
        $this->assertTrue($anyRoute->match(new Request('GET', '/')));
        $this->assertTrue($anyRoute->match(new Request('POST', '/')));

        // Specific path - any method
        $postRoute = new Route('/A', $dummyCallback);
        $this->assertFalse($postRoute->match(new Request('GET', '/')));
        $this->assertTrue($postRoute->match(new Request('GET', '/A')));

        // Specific path - Specific method
        $postRoute = new Route('/A', $dummyCallback, ['POST']);
        $this->assertFalse($postRoute->match(new Request('GET', '/A')));
        $this->assertTrue($postRoute->match(new Request('POST', '/A')));


        // Support for end-slash...
        $endSlashRoute = new Route('/A/', $dummyCallback);
        $this->assertFalse($endSlashRoute->match(new Request('GET', '/A')));
        $this->assertTrue($endSlashRoute->match(new Request('GET', '/A/')));

        $route = new Route('/A', $dummyCallback);
        $this->assertFalse($route->match(new Request('GET', '/A/')));
        $this->assertTrue($route->match(new Request('GET', '/A')));
    }

    const FORMAT_SAMPLES = [
        'int'      => '/5',
        'float'    => '/5.398',
        'any'      => "/I'am a complete sentence !",
        'date'     => '/2023-07-16',
        'time'     => '/16:20:00',
        'datetime' => '/2000-10-01 15:00:00',
        'hex'      => '/e4ae73fb11fd',
        'uuid'     => '/123e4567-e89b-12d3-a456-426655440000'
    ];

    protected function genericSlugFormatTest(
        string $routePath,
        string $successRequestPath,
        array $failRequestPath,
    ) {
        $route = Route::get($routePath, fn()=>false);

        foreach ($failRequestPath as $path)
        {
            $req = new Request('GET', $path);
            $this->assertFalse($route->match($req), "Failed fail Request for [$routePath] route");
        }

        $req = new Request('GET', $successRequestPath);
        $this->assertTrue($route->match($req), "Failed success Request for [$routePath] route");
    }

    public function test_slugFormats()
    {
        $samples = self::FORMAT_SAMPLES;

        $samplesWithout = function($keys) use ($samples) {
            $copy = $samples;
            foreach ($keys as $k)
                unset($copy[$k]);
            return array_values($copy);
        };

        $this->genericSlugFormatTest('/{int:x}',      $samples['int'],      $samplesWithout(['int']));
        $this->genericSlugFormatTest('/{float:x}',    $samples['float'],    $samplesWithout(['float', 'int']));
        $this->genericSlugFormatTest('/{any:x}',      $samples['any'],      []);
        $this->genericSlugFormatTest('/{date:x}',     $samples['date'],     $samplesWithout(['date']));
        $this->genericSlugFormatTest('/{time:x}',     $samples['time'],     $samplesWithout(['time']));
        $this->genericSlugFormatTest('/{datetime:x}', $samples['datetime'], $samplesWithout(['datetime']));
        $this->genericSlugFormatTest('/{hex:x}',      $samples['hex'],      $samplesWithout(['hex', 'int']));
        $this->genericSlugFormatTest('/{uuid:x}',     $samples['uuid'],     $samplesWithout(['uuid']));
    }
}