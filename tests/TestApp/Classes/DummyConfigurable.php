<?php

namespace YonisSavary\Sharp\Tests\TestApp\Classes;

use YonisSavary\Sharp\Classes\Core\Configurable;

class DummyConfigurable
{
    use Configurable;

    public function __construct()
    {

    }

    public static function getDefaultConfiguration()
    {
        return [
            'enabled' => true,
            'cached' => false
        ];
    }
}