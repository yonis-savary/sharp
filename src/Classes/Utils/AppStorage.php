<?php 

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Env\Storage;

trait AppStorage
{
    protected static ?Storage $instance = null;

    public static function &get(): Storage
    {
        self::$instance ??= 
            Storage::getInstance()->getSubStorage("Sharp/AppStorages/".md5(get_called_class()));
        return self::$instance;
    }
}