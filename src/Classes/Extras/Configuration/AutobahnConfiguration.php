<?php

namespace YonisSavary\Sharp\Classes\Extras\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;
use YonisSavary\Sharp\Classes\Extras\AutobahnDrivers\BaseDriver;
use YonisSavary\Sharp\Classes\Extras\AutobahnDrivers\DriverInterface;

class AutobahnConfiguration
{
    use ConfigurationElement;

    public function __construct(
        public readonly DriverInterface $driver = new BaseDriver()
    ){}
}