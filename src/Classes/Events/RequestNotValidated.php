<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;

class RequestNotValidated extends AbstractEvent
{
    public function __construct(
        public array $errors
    ){}
}