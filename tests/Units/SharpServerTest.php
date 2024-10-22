<?php

namespace YonisSavary\Sharp\Tests\Units;

use RuntimeException;
use YonisSavary\Sharp\Classes\Test\SharpServer;
use YonisSavary\Sharp\Classes\Test\SharpTestCase;

class SharpServerTest extends SharpTestCase
{
    public function test_assertJsonResponse()
    {
        $this->assertTrue(true);

        $response = $this->fetch(
            "GET", "/root"
        );

        $response->logSelf();

        $this->assertJsonResponse("Hello!", "GET", "/root");
    }

    public function test_startAndStop()
    {
        $server = new SharpServer();

        $this->assertTrue($server->isRunning());
        $this->assertJsonResponse("Hello!", "GET", "/root", server: $server);

        $server->stop();
        $this->assertFalse($server->isRunning());

        $this->expectException(RuntimeException::class); // Cannot connect to server
        $this->assertJsonResponse("Hello!", "GET", "/root", server: $server);
    }
}