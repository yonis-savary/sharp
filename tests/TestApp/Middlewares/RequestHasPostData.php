<?php

namespace YonisSavary\Sharp\Tests\TestApp\Middlewares;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;

class RequestHasPostData implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        $post = $request->post();
        return count($post) ? $request: Response::json("Response must have POST data");
    }
}