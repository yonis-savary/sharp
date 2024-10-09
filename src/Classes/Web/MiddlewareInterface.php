<?php

namespace YonisSavary\Sharp\Classes\Web;

use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

/**
 * Middlewares can be attached to any route to add checking layers
 * See `handle()` method docs for more
 */
interface MiddlewareInterface
{
    /**
     * @param Request $request `Request` to (in)validate
     * @return Request|Response Return a `Request` to validate, a `Response` to display it and kill the request
     */
    public static function handle(Request $request): Request|Response;
}