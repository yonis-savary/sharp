<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

trait AppStorage
{
    protected static ?Storage $instance = null;

    final protected static function getUniqueName(): string
    {
        $relativePath = str_replace(Autoloader::projectRoot(). "/", "", Utils::classnameToPath(get_called_class()));
        return strtolower(str_replace("/", ".", $relativePath));
    }

    public static function &get(): Storage
    {
        self::$instance ??=
            Storage::getInstance()->getSubStorage('App/Storages/'.self::getUniqueName());
        return self::$instance;
    }
}