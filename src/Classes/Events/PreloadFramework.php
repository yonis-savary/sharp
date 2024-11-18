<?php

namespace YonisSavary\Sharp\Classes\Events;

use YonisSavary\Sharp\Classes\Core\AbstractEvent;

class PreloadFramework extends AbstractEvent
{
    public function __construct(
        public string $rootDirectory
    ){}
}