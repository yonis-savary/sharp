<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Storage;

trait AppCache
{
    protected static ?Cache $instance = null;

    public static function &get(): Cache
    {
        self::$instance ??= new Cache(
            Storage::getInstance()->getSubStorage('Cache/Sharp/AppCaches/'. md5(get_called_class()))
        );
        return self::$instance;
    }
}