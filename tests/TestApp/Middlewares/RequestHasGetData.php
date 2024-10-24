<?php

namespace YonisSavary\Sharp\Tests\TestApp\Middlewares;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;

class RequestHasGetData implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        return count($request->get()) ?
            $request:
            Response::json('Response must have GET data');
    }
}