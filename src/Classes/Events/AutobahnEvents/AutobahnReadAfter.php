<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered after reading row(s) with Autobahn
 */
class AutobahnReadAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public DatabaseQuery &$query,
        public array $results=[]
    ){}
}