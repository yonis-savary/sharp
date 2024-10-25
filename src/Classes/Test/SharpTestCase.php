<?php

namespace YonisSavary\Sharp\Classes\Test;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

abstract class SharpTestCase extends TestCase
{
    /**
     * Fetch an URL of your application and return a `Response` object
     */
    public function fetch(
        string $method,
        string $url,
        array $getParams=[],
        array $postParams=[],
        array $headers=[],
        SharpServer $server = null
    ): Response {

        $server ??= SharpServer::getInstance();
        $url = $server->getURL($url);

        return (new Request(
            $method,
            $url,
            $getParams,
            $postParams,
            $headers
        ))->fetch();
    }

    /**
     * Fetch content from your application,
     * assert the response is JSON and match the `$expected` content
     */
    public function assertJsonResponse(
        mixed $expected,
        string $method,
        string $url,
        array $getParams=[],
        array $postParams=[],
        array $headers=[],
        SharpServer $server=null
    ) {
        $response = $this->fetch($method, $url, $getParams, $postParams, $headers, $server);

        $this->assertTrue(
            $response->isJSON()
        );

        $this->assertEquals(
            $expected,
            $response->getContent()
        );
    }

    public function assertResponseCode(
        int $responseCode,
        string $method,
        string $url,
        array $getParams=[],
        array $postParams=[],
        array $headers=[],
        SharpServer $server=null
    ){
        $response = $this->fetch($method, $url, $getParams, $postParams, $headers, $server);
        $this->assertEquals($responseCode, $response->getResponseCode());
    }

    public function assertNotFound(
        string $method,
        string $url,
        array $getParams=[],
        array $postParams=[],
        array $headers=[],
        SharpServer $server=null
    ){
        $this->assertResponseCode(404, $method, $url, $getParams, $postParams, $headers, $server);
    }


    /**
     * Fetch content from your application,
     * assert the response is HTML and match the `$expected` content
     */
    public function assertHTMLResponse(
        mixed $expected,
        string $method,
        string $url,
        array $getParams=[],
        array $postParams=[],
        array $headers=[],
        SharpServer $server=null
    ) {
        $response = $this->fetch($method, $url, $getParams, $postParams, $headers, $server);

        $this->assertEquals(
            'text/html',
            $response->getHeader('content-type')
        );

        $this->assertEquals(
            $expected,
            $response->getContent()
        );
    }
}