<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\DatabaseQuery;

/**
 * This event is triggered before deleting row(s) with Autobahn
 */
class AutobahnDeleteBefore extends AbstractEvent
{
    public function __construct(
        public string $model,
        public DatabaseQuery &$query
    ){}
}