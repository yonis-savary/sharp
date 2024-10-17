<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\ModelQuery;

/**
 * This event is triggered before reading row(s) with Autobahn
 */
class AutobahnReadBefore extends AbstractEvent
{
    public function __construct(
        public string $model,
        public ModelQuery &$query
    ){}
}