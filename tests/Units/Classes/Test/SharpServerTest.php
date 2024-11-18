<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Test;

use YonisSavary\Sharp\Classes\Test\SharpServer;
use YonisSavary\Sharp\Classes\Test\SharpTestCase;

/**
 * ShapServer is an object respresenting a PHP Built-in server process
 */
class SharpServerTest extends SharpTestCase
{
    public function test_getPort()
    {
        $server = new SharpServer(8080);
        $this->assertEquals(8080, $server->getPort());
        $server->stop();

        $server = new SharpServer();
        $port = $server->getPort();
        $this->assertTrue(8000 <= $port && $port <= 65534);
    }


    public function test_startAndStop()
    {
        $server = new SharpServer();

        $this->assertTrue($server->isRunning());
        $server->stop();
        $this->assertFalse($server->isRunning());
    }

    public function test_getURL()
    {
        $server = new SharpServer(8080, hostname: "127.0.0.1", protocol: "https");
        $this->assertEquals("https://127.0.0.1:8080/test", $server->getURL("/test"));

        $server = new SharpServer();
        $this->assertStringEndsWith("/test", $server->getURL("/test"));
    }

    public function test_getOutput()
    {
        $server = new SharpServer();

        $this->fetch("GET", "/math/double/1", server: $server);
        $output = $server->getErrorOutput();
        $this->assertCount(4, explode("\n", $output));
    }
}