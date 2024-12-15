<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

trait AppCache
{
    protected static ?Cache $instance = null;

    final protected static function getUniqueName(): string
    {
        $relativePath = str_replace(Autoloader::projectRoot(). "/", "", Utils::classnameToPath(get_called_class()));
        return strtolower(str_replace("/", ".", $relativePath));
    }

    public static function &get(): Cache
    {
        self::$instance ??= new Cache(
            Storage::getInstance()->getSubStorage('Cache/App/Caches/'. self::getUniqueName())
        );
        return self::$instance;
    }
}