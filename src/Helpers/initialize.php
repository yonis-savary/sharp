<?php

/*
    This file execute code that need to be called as the framework initialize itself
    (Used for process that need to be fast and optimizations)
*/

use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Events\LoadedFramework;
use YonisSavary\Sharp\Classes\Extras\AssetServer;
use YonisSavary\Sharp\Classes\Web\Router;


EventListener::getInstance()->on(LoadedFramework::class, function(){
    // AssertServer don't use a Route object to handle request
    // It process it as soon as the framework loads
    AssetServer::getInstance();

    // The quick routing route the request as soon as it arrives
    // The router need to be cached in order to use the quick-routing
    // See doc. for more
    Router::getInstance()->executeQuickRouting();

});