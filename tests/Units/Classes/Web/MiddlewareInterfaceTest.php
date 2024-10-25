<?php

namespace YonisSavary\Sharp\Tests\Units\Classes\Web;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

/**
 * Middlewares can be attached to any route to add checking layers
 * See `handle()` method docs for more
 */
class MiddlewareInterfaceTest extends TestCase
{
    public function test_handle()
    {
        $hasParamMiddleware = new class implements MiddlewareInterface
        {
            public static function handle(Request $request): Request|Response
            {
                return count($request->get() ?? []) ?
                    $request:
                    Response::json("GET params needed !");
            }
        };

        $this->assertInstanceOf(
            Response::class,
            $hasParamMiddleware::handle(new Request("GET", "/"))
        );
        $this->assertInstanceOf(
            Request::class,
            $hasParamMiddleware::handle(new Request("GET", "/", ["my-search" => "some-subject"]))
        );
    }
}