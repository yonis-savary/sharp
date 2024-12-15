<?php


namespace YonisSavary\Sharp\Tests\Units\Classes\Http;

use CurlHandle;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Events\RequestNotValidated;
use YonisSavary\Sharp\Classes\Http\Classes\Validator;
use YonisSavary\Sharp\Classes\Http\Classes\UploadFile;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Test\SharpServer;
use YonisSavary\Sharp\Classes\Test\SharpTestCase;
use YonisSavary\Sharp\Classes\Web\Route;

/**
 * This component purpose is to hold information about a HTTP Request,
 * a default one can be built with `Request::fromGlobals()`
 */
class RequestTest extends SharpTestCase
{
    public function test_logSelf()
    {
        $outputLogger = Logger::fromStream(fopen("php://output", "w"));
        $req = new Request("POST", "/blablabla");

        ob_start();
        $req->logSelf($outputLogger);
        $this->assertStringContainsString("POST /blablabla", ob_get_clean());
    }

    protected function mockPHPUpload(int $n=1, string $inputName='uploads'): array
    {
        $storage = Storage::getInstance();
        $files = [];
        for ($i=0; $i<$n; $i++)
        {
            $name = uniqid('upload');
            $path = $storage->path("uploads/$name");
            $content = 'content';
            $storage->write("uploads/$name", $content);

            $files[] = [
                'name' => $name,
                'type' => 'text/plain',
                'tmp_name' => $path,
                'error' => UPLOAD_ERR_OK,
                'size' => strlen($content)
            ];
        }

        if ($n > 1)
        {
            $uploads = [$inputName => [
                'name'     => [],
                'type'     => [],
                'tmp_name' => [],
                'error'    => [],
                'size'     => []
            ]];
            foreach ($uploads[$inputName] as $key => &$value)
                $value = array_map(fn($x) => $x[$key], $files);

            return $uploads;
        }
        else
        {
            // Single upload
            return [$inputName => $files[0]];
        }
    }

    protected function sampleGetRequest(): Request
    {
        return new Request('GET', '/view', ['A' => 1]);
    }

    protected function samplePostRequest(bool $multipleUploads=false): Request
    {
        return new Request('POST', '/form', ['A' => 1], ['B' => 2], $this->mockPHPUpload($multipleUploads ? 5:1));
    }

    public function test___construct()
    {
        $req = new Request(
            'POST',
            '/accept',
            ['path' => 'root'],
            ['body' => 'post-data'],
            $this->mockPHPUpload(1),
            ['Content-Type' => 'application/json'],
            '{"A": 5}'
        );

        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals('/accept', $req->getPath());
        $this->assertEquals(['path' => 'root'], $req->get());
        $this->assertEquals(['body' => 'post-data'], $req->post());
        $this->assertEquals(['A' => 5], $req->body());
        $this->assertCount(1, $req->getUploads());
        $this->assertEquals(['content-type' => 'application/json'], $req->getHeaders());
    }

    public function test_fromGlobals()
    {
        $this->assertInstanceOf(
            Request::class,
            Request::fromGlobals()
        );
    }

    public function test_post()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals([], $get->post());
        $this->assertEquals(['B' => 2], $post->post());
    }

    public function test_get()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(['A' => 1], $get->get());
        $this->assertEquals(['A' => 1], $post->get());
    }


    public function test_body()
    {
        $request = new Request("POST", "/", body:"I'm a form");
        $this->assertEquals("I'm a form", $request->body());

        $request = new Request("POST", "/");
        $this->assertNull($request->body());
    }

    public function test_all()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(['A' => 1], $get->all());
        $this->assertEquals(['A' => 1, 'B' => 2], $post->all());
    }

    public function test_list()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        list($A, $B, $C) = $get->list('A', 'B', 'C');
        $this->assertEquals(1, $A);
        $this->assertNull($B);
        $this->assertNull($C);

        list($A, $B, $C) = $post->list('A', 'B', 'C');
        $this->assertEquals(1, $A);
        $this->assertEquals(2, $B);
        $this->assertNull($C);
    }

    public function test_params()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(1, $get->params('A'));
        $this->assertNull($get->params('B'));
        $this->assertNull($get->params('C'));

        $this->assertEquals(1, $post->params('A'));
        $this->assertEquals(2, $post->params('B'));
        $this->assertNull($post->params('C'));
    }

    public function test_paramsFromGet()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals(1, $get->paramsFromGet('A'));
        $this->assertNull($get->paramsFromGet('B'));
        $this->assertNull($get->paramsFromGet('C'));

        $this->assertEquals(1, $post->paramsFromGet('A'));
        $this->assertNull($post->paramsFromGet('B'));
        $this->assertNull($post->paramsFromGet('C'));
    }

    public function test_paramsFromPost()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertNull($get->paramsFromPost('A'));
        $this->assertNull($get->paramsFromPost('B'));
        $this->assertNull($get->paramsFromPost('C'));

        $this->assertNull($post->paramsFromPost('A'));
        $this->assertEquals(2, $post->paramsFromPost('B'));
        $this->assertNull($post->paramsFromPost('C'));
    }


    public function test_has()
    {
        $request = new Request("GET", "/", ["A" => 1, "B" => 2, "C" => null]);

        $this->assertFalse($request->has("C"));
        $this->assertTrue($request->has("C", true));

        $this->assertTrue($request->has(["A", "B"]));
        $this->assertTrue($request->has(["A", "B"], true));

        $this->assertFalse($request->has(["A", "B", "C"]));
        $this->assertTrue($request->has(["A", "B", "C"], true));
    }

    public function test_integer()
    {
        $dispatched = false;
        $listener = EventListener::getInstance();
        $listener->removeAllForEvent(RequestNotValidated::class);
        $listener->on(RequestNotValidated::class, function() use (&$dispatched) {
            $dispatched = true;
        });

        $request = new Request("GET", "/", ["A" => "qskqsdml", "B" => "12", "C" => "0"]);

        $this->assertEquals(0, $request->integer("A", false));
        $this->assertEquals(12, $request->integer("B", false));
        $this->assertEquals(0, $request->integer("C", false));

        $dispatched = false;
        $this->assertEquals(-1, $request->integer("A"));
        $this->assertTrue($dispatched);
        $this->assertEquals(12, $request->integer("B"));
        $this->assertEquals(0, $request->integer("C"));


        $dispatched = false;
        $this->assertEquals(-1, $request->integer("D"));
        $this->assertTrue($dispatched);
    }


    public function test_float()
    {
        $dispatched = false;
        $listener = EventListener::getInstance();
        $listener->removeAllForEvent(RequestNotValidated::class);
        $listener->on(RequestNotValidated::class, function() use (&$dispatched) {
            $dispatched = true;
        });

        $request = new Request("GET", "/", ["A" => "qskqsdml", "B" => "12.5", "C" => "0"]);

        $this->assertEquals(0, $request->float("A", false));
        $this->assertEquals(12.5, $request->float("B", false));
        $this->assertEquals(0, $request->float("C", false));

        $dispatched = false;
        $this->assertEquals(-1, $request->float("A"));
        $this->assertTrue($dispatched);
        $this->assertEquals(12.5, $request->float("B"));
        $this->assertEquals(0, $request->float("C"));


        $dispatched = false;
        $this->assertEquals(-1, $request->float("D"));
        $this->assertTrue($dispatched);
    }

    public function test_json()
    {
        $myObject = ["i'm an example" => ['Of a JSON object' => ['That holds' => ['Some value', 950983]]]];
        $myObjectString = json_encode($myObject);

        $dispatched = false;
        $listener = EventListener::getInstance();
        $listener->removeAllForEvent(RequestNotValidated::class);
        $listener->on(RequestNotValidated::class, function() use (&$dispatched) {
            $dispatched = true;
        });

        $request = new Request("GET", "/", ["object" => $myObjectString]);

        $this->assertEquals($myObject, $request->json("object"));

        $dispatched = false;
        $this->assertEquals(-1, $request->float("inexistant"));
        $this->assertTrue($dispatched);
    }

    public function test_getMethod()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals('GET', $get->getMethod());
        $this->assertEquals('POST', $post->getMethod());
    }

    public function test_getPath()
    {
        $get = $this->sampleGetRequest();
        $post = $this->samplePostRequest();

        $this->assertEquals('/view', $get->getPath());
        $this->assertEquals('/form', $post->getPath());
    }

    public function test_getHeaders()
    {
        $req = new Request('GET', '/', [], [], [], ['H1' => 'V1']);
        $this->assertEquals(['h1' => 'V1'], $req->getHeaders());
    }

    public function test_getUploads()
    {
        $req = $this->samplePostRequest(true);
        $this->assertCount(5, $req->getUploads());
        foreach ($req->getUploads() as $upload)
            $this->assertInstanceOf(UploadFile::class, $upload);

        $req = $this->samplePostRequest();
        $this->assertCount(1, $req->getUploads());
        foreach ($req->getUploads() as $upload)
            $this->assertInstanceOf(UploadFile::class, $upload);

        $req = $this->samplePostRequest();
        $req->setUploads(
            new UploadFile(UploadFileTest::getDummyPHPUpload(), 'documents'),
            new UploadFile(UploadFileTest::getDummyPHPUpload(), 'documents'),
            new UploadFile(UploadFileTest::getDummyPHPUpload(), 'documents'),
            new UploadFile(UploadFileTest::getDummyPHPUpload(), 'pictures'),
            new UploadFile(UploadFileTest::getDummyPHPUpload(), 'pictures'),
        );

        $this->assertCount(2, $req->getUploads('pictures'));
        $this->assertCount(3, $req->getUploads('documents'));
    }

    public function test_getIp()
    {
        $request = new Request("GET", "/", ip: "127.0.0.1");
        $this->assertEquals("127.0.0.1", $request->getIp());

        $request = new Request("GET", "/");
        $this->assertNull($request->getIp());
    }

    public function test_setSlugs()
    {
        $req = $this->sampleGetRequest();

        $req->setSlugs(['name' => 'value']);
        $this->assertEquals(['name' => 'value'], $req->getSlugs());
    }

    public function test_getSlugs()
    {
        $req = $this->sampleGetRequest();

        $this->assertEquals([], $req->getSlugs());

        $req->setSlugs(['name' => 'value']);
        $this->assertEquals(['name' => 'value'], $req->getSlugs());
    }

    public function test_getSlug()
    {
        $req = $this->sampleGetRequest();

        $req->setSlugs(['name' => 'value', 'nullKey' => null]);
        $this->assertEquals('value', $req->getSlug('name'));
        $this->assertNull($req->getSlug('nullKey'));
        $this->assertNull($req->getSlug('nullKey', -1));
        $this->assertEquals(-1, $req->getSlug('inexistent', -1));
    }

    public function test_setRoute()
    {
        $route = new Route('/', fn()=>'null');
        $req = new Request('GET', '/');

        $req->setRoute($route);
        $this->assertEquals($req->getRoute(), $route);
    }

    public function test_getRoute()
    {
        $route = new Route('/', fn()=>'null');
        $req = new Request('GET', '/');

        $this->assertNull($req->getRoute());
        $req->setRoute($route);
        $this->assertEquals($req->getRoute(), $route);
    }

    public function test_unset()
    {
        $req = $this->samplePostRequest();

        $this->assertEquals(['A' => 1, 'B' => 2], $req->all());
        $req->unset('B');

        $this->assertEquals(['A' => 1], $req->all());
        $req->unset('A');

        $this->assertEquals([], $req->all());
    }


    public function test_getCurlHandle()
    {
        $req = $this->samplePostRequest();

        $handle = $req->toCurlHandle();

        $this->assertInstanceOf(CurlHandle::class, $handle);
        /** @todo Find a way to test CurlHandle and fetch() method */
    }

    public function test_validate()
    {
        $dispatcher = new EventListener;

        $dispatchedEvent = false;
        $dispatcher->on(RequestNotValidated::class, function() use (&$dispatchedEvent) {
            $dispatchedEvent = true;
        });

        $request = new Request("GET", "/", [], ["some-string" => " bla bla bla      ", "some-int" => "12"]);

        $form = $request->validate([
            "some-string" => Validator::string(),
            "some-int" => Validator::integer()
        ], $dispatcher);

        $this->assertFalse($dispatchedEvent);
        $this->assertEquals("bla bla bla", $form["some-string"]);
        $this->assertEquals(12, $form["some-int"]);


        $request = new Request("GET", "/", [], ["some-date" => "Not a date at all"]);
        $form = $request->validate([
            "some-date" => Validator::date()
        ], $dispatcher);

        $this->assertNull($form);
        $this->assertTrue($dispatchedEvent);
    }


    public function test_isJSON()
    {
        $request = new Request("GET", "/", headers: ["content-type" => "application/json"]);
        $this->assertTrue($request->isJSON());

        $request = new Request("GET", "/", headers: ["content-type" => "text/html"]);
        $this->assertFalse($request->isJSON());
    }


    public function test_fetch()
    {
        $server = SharpServer::getInstance();

        $request = new Request("GET", $server->getURL("/math/double/2"));
        $response = $request->fetch();
        $this->assertEquals(4, $response->getContent());

        $request = new Request("POST", $server->getURL("/math/multiply"), [], ["a" => 5, "b" => 3]);
        $response = $request->fetch();
        $this->assertEquals(15, $response->getContent());
    }

    public function test_toCurlHandle()
    {
        $request = new Request("GET", "/");
        $this->assertInstanceOf(CurlHandle::class, $request->toCurlHandle());
    }

    public function test_getLastFetchDuration()
    {
        $server = SharpServer::getInstance();

        $request = new Request("GET", $server->getURL("/math/double/1"));
        $response = $request->fetch();
        $this->assertEquals(2, $response->getContent());

        $request = new Request("GET", $server->getURL("/utils/sleep"));
        $response = $request->fetch();
        $this->assertEquals("ok", $response->getContent());
        $this->assertGreaterThan(500, $request->getLastFetchDuration());

    }
}