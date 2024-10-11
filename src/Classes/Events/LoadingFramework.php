<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;

/**
 * This event is triggered while the framework and applications are being loaded
 */
class LoadingFramework extends AbstractEvent
{
    public function __construct()
    {}
}