<?php

namespace YonisSavary\Sharp\Tests\Integration;

use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;

class IntegrationAppFactory
{
    protected static ?Storage $sampleStorage = null;

    /**
     * Create an empty sharp application
     */
    public static function createPlainSharpApp(): Storage
    {
        $integrationStorage = new Storage(__DIR__. "/../Integration-apps");

        if (!self::$sampleStorage)
        {
            $appName = uniqid("Origin", true);
            self::$sampleStorage = $integrationStorage->getSubStorage($appName);

            $sharpRepositoryRoot = realpath(__DIR__. "/../..");

            self::$sampleStorage->write("composer.json", Terminal::stringToFile(
            '{
                "require": {
                    "yonis-savary/sharp": "dev-main"
                },
                "repositories": [
                    {
                        "type": "path",
                        "url": "'. $sharpRepositoryRoot .'",
                        "options": {
                            "symlink": false
                        }
                    }
                ]
            }
            ', 2));

            $origin = getcwd();
            chdir(self::$sampleStorage->getRoot());
            shell_exec("composer install > /dev/null 2>&1");
            chdir($origin);

            register_shutdown_function(function(){
                shell_exec("rm -r " . self::$sampleStorage->getRoot());
            });
        }


        $appName = uniqid("Application");
        $appStorage = $integrationStorage->getSubStorage($appName);

        $originDir = self::$sampleStorage->getRoot();
        $targetDir = $appStorage->getRoot();
        shell_exec("cp -r $originDir/* $targetDir/ > /dev/null 2>&1");

        return $appStorage;
    }
}