<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\Route;

/**
 * This event is triggered when `Router` cannot find a route matching a request
 */
class RouteReturnedResponse extends AbstractEvent
{
    public function __construct(
        public Route &$route,
        public Response &$response,
        public mixed &$rawResponse,
        public Request &$request
    ){}
}