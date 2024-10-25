<?php

namespace YonisSavary\Sharp\Tests\Classes\Extras;

use Exception;
use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnCreateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnCreateBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnReadAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnReadBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateBefore;
use YonisSavary\Sharp\Classes\Extras\Autobahn;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShow;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestTvShowProducer;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestUserData;
use YonisSavary\Sharp\Tests\Units\TestClassFactory;

class AutobahnTest extends TestCase
{
    protected function createAutobahn(): array
    {
        $db = TestClassFactory::createDatabase();
        return [new Autobahn($db), $db];
    }

    public function test_all()
    {
        list($autobahn, $db) = $this->createAutobahn();
        $router = new Router;

        $router->addRoutes(
            ...$autobahn->all(TestTvShow::class)
        );

        $this->assertCount(5, $router->getRoutes());

    }

    public function test_create()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnCreateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnCreateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        /** @var Autobahn $autobahn */
        /** @var Database $database */
        list($autobahn, $database) = $this->createAutobahn();
        $router = new Router();

        $router->addRoutes($autobahn->create(TestTvShow::class));
        $this->assertCount(1, $router->getRoutes());

        $nextId = $database->query('SELECT MAX(id)+1 as next FROM test_tv_show')[0]['next'];


        $response = $router->route(
            new Request('POST', '/test_tv_show', [], ['name' => 'Rick & Morty', 'episode_number' => 71])
        );

        $this->assertEquals($nextId, $response->getContent()['insertedId'][0]);
        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);

        $response = $router->route(
            new Request('POST', '/test_tv_show',
            headers: ['content-type' => 'application/json'],
            body: json_encode([
                ["name" => 'The Fresh Prince of Bel-Air', "episode_number" => 148],
                ["name" => 'Trailer Park Boys', "episode_number" => 105],
            ]))
        );

        $this->assertEquals([$nextId+1, $nextId+2], $response->getContent()['insertedId']);

    }

    public function test_multipleCreate()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnMultipleCreateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnMultipleCreateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        /** @var Autobahn $autobahn */
        /** @var Database $database */
        list($autobahn, $database) = $this->createAutobahn();
        $router = new Router();
        $router->addRoutes(
            $autobahn->createMultiples(TestTvShow::class)
        );
        $this->assertCount(1, $router->getRoutes());

        $response = $router->route(
            new Request('POST', '/test_tv_show/create-multiples', body: [
                ["name" => 'The Fresh Prince of Bel-Air', "episode_number" => 148],
                ["name" => 'Trailer Park Boys', "episode_number" => 105],
            ])
        );

        $insertedIds = $response->getContent()['insertedId'];
        $this->assertEquals([6, 7], $insertedIds);

        $this->expectException(Exception::class);
        $response = $router->route(
            new Request('POST', '/test_tv_show/create-multiples', body: [
                ['name' => 'The simpsons', 'episode_number' => 771],
                ['name' => 'Squid Game'], // Missing episode_number, throws exception
            ])
        );

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }


    public function test_read()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnReadBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnReadAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        /** @var Autobahn $autobahn */
        /** @var Database $database */
        list($autobahn, $database) = $this->createAutobahn();
        $router = new Router();
        $router->addRoutes(
            $autobahn->read(TestTvShowProducer::class)
        );
        $this->assertCount(1, $router->getRoutes());


        $res = $router->route(new Request('GET', '/test_tv_show_producer'));
        $this->assertCount(24, $res->getContent());

        $res = $router->route(new Request('GET', '/test_tv_show_producer', ['tv_show' => 1]));
        $this->assertCount(2, $res->getContent());

        $res = $router->route(new Request('GET', '/test_tv_show_producer', ['tv_show' => [1,2]]));
        $this->assertCount(5, $res->getContent());

        $data = $res->getContent()[0];

        $this->assertArrayHasKey('data',    $data);
        $this->assertArrayHasKey('name',    $data['data']);
        $this->assertArrayHasKey('tv_show', $data);
        $this->assertArrayHasKey('data',    $data['tv_show']);
        $this->assertArrayHasKey('id',      $data['tv_show']['data']);

        $res = $router->route(new Request('GET', '/test_tv_show_producer', ['_ignores' => ['test_tv_show_producer&tv_show']]));
        $data = $res->getContent()[0];
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertArrayNotHasKey('tv_show', $data);

        $res = $router->route(new Request('GET', '/test_tv_show_producer', ['_join' => false]));
        $data = $res->getContent()[0];
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('name', $data['data']);
        $this->assertArrayNotHasKey('tv_show', $data);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }

    public function test_update()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnUpdateBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnUpdateAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        /** @var Autobahn $autobahn */
        /** @var Database $database */
        list($autobahn, $database) = $this->createAutobahn();
        $router = new Router();
        $router->addRoutes(
            $autobahn->update(TestTvShow::class)
        );
        $this->assertCount(1, $router->getRoutes());

        $router->route(new Request('PUT', '/test_tv_show', ['id' => 1, 'episode_number' => 0]));
        $this->assertEquals(0, $database->query('SELECT episode_number FROM test_tv_show WHERE id = 1')[0]['episode_number']);

        $router->route(new Request('PUT', '/test_tv_show', ['id' => 2, 'episode_number' => 999]));
        $this->assertEquals(999, $database->query('SELECT episode_number FROM test_tv_show WHERE id = 2')[0]['episode_number']);

        $router->route(new Request('PUT', '/test_tv_show', ['id' => [1,2], 'episode_number' => 555]));
        $this->assertEquals(555, $database->query('SELECT episode_number FROM test_tv_show WHERE id = 1')[0]['episode_number']);
        $this->assertEquals(555, $database->query('SELECT episode_number FROM test_tv_show WHERE id = 2')[0]['episode_number']);

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);
    }

    public function test_delete()
    {
        $dispatchedBeforeEvent = false;
        $dispatchedAfterEvent = false;
        EventListener::getInstance()->on(AutobahnDeleteBefore::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedBeforeEvent = (!$dispatchedAfterEvent); });
        EventListener::getInstance()->on(AutobahnDeleteAfter::class, function() use (&$dispatchedBeforeEvent, &$dispatchedAfterEvent) { $dispatchedAfterEvent = $dispatchedBeforeEvent; });

        /** @var Autobahn $autobahn */
        /** @var Database $database */
        list($autobahn, $database) = $this->createAutobahn();
        $router = new Router();
        $router->addRoutes(
            $autobahn->delete(TestTvShow::class)
        );
        $this->assertCount(1, $router->getRoutes());


        $router->route(new Request('DELETE', '/test_tv_show', ['id' => 1]));
        $this->assertCount(4, $database->query("SELECT * FROM test_tv_show"));

        $this->assertTrue($dispatchedBeforeEvent);
        $this->assertTrue($dispatchedAfterEvent);

        # Dangerous query prevention, no change made
        $router->route(new Request('DELETE', '/test_tv_show'));
        $this->assertCount(4, $database->query("SELECT * FROM test_tv_show"));

        $router->route(new Request('DELETE', '/test_tv_show', ['id' => [2, 3]]));
        $this->assertCount(2, $database->query("SELECT * FROM test_tv_show"));
    }
}