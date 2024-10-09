<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;

/**
 * This event is triggered when `Router` cannot find a route matching a request
 */
class RouteNotFound extends AbstractEvent
{
    public function __construct(
        public Request &$request,
        public Response &$response
    ){}
}