<?php

namespace YonisSavary\Sharp\Tests\Classes;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\MiddlewareInterface;

class MiddlewareB implements MiddlewareInterface
{
    public static function handle(Request $request): Request|Response
    {
        return $request;
    }
}