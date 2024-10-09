<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Web\Route;

/**
 * This event is triggered when a Router is about to call a route's callback
 */
class RoutedRequest extends AbstractEvent
{
    public function __construct(
        public Request $request,
        public Route $route
    ) {}
}