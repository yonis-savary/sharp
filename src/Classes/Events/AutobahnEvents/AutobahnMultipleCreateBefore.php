<?php

namespace YonisSavary\Sharp\Classes\Events\AutobahnEvents;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

/**
 * This event is triggered before inserting multiple rows with Autobahn
 */
class AutobahnMultipleCreateBefore extends AbstractEvent
{
    public function __construct(
        public ObjectArray $dataToBeInserted
    ){}
}