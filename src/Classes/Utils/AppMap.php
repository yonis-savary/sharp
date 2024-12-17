<?php

namespace YonisSavary\Sharp\Classes\Utils;

use YonisSavary\Sharp\Classes\Core\GenericMap;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

trait AppMap
{
    protected static ?GenericMap $instance = null;

    final protected static function getUniqueName(): string
    {
        $relativePath = str_replace(Autoloader::projectRoot(). "/", "", Utils::classnameToPath(get_called_class()));
        return strtolower(str_replace("/", ".", $relativePath));
    }

    final protected static function getAppMapsStorage(): Storage
    {
        return Storage::getInstance()->getSubStorage('App/Maps');
    }

    public static function &get(): GenericMap
    {
        if (self::$instance === null)
        {
            $hashName = self::getUniqueName();
            $storage = self::getAppMapsStorage();

            $data = [];
            if ($storage->isFile($hashName))
                $data = unserialize($storage->read($hashName));

            self::$instance = new AppMapInstance($storage, $hashName, $data);
        }

        return self::$instance;
    }
}