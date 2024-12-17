<?php

use YonisSavary\Sharp\Classes\Env\Configuration\Configuration;
use YonisSavary\Sharp\Classes\Env\Configuration\GenericConfiguration;

return [
    Configuration::fromFile("another-config.php"),

    new GenericConfiguration("complex-config", [
        "key" => "value"
    ])
];