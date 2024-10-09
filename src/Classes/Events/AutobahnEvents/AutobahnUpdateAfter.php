<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered after updating row(s) with Autobahn
 */
class AutobahnUpdateAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public mixed $primaryKeyValue,
        public DatabaseQuery &$query
    ){}
}