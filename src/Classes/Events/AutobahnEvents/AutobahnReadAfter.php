<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\ModelQuery;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

/**
 * This event is triggered after reading row(s) with Autobahn
 */
class AutobahnReadAfter extends AbstractEvent
{
    public function __construct(
        public string $model,
        public ModelQuery &$query,
        public ObjectArray $results
    ){}
}