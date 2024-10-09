<?php

namespace YonisSavary\Sharp\Tests\Middlewares;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;

class RequestHasGetData implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        $get = $request->get();
        return count($get) ? $request: Response::json("Response must have GET data");
    }
}