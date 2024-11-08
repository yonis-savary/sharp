<?php

namespace YonisSavary\Sharp\Tests\Integration;

use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Storage;

class IntegrationAppFactory
{
    /**
     * Create an empty sharp application
     */
    public static function createPlainSharpApp(): Storage
    {
        $integrationStorage = new Storage(__DIR__. "/../Integration-apps");

        $appName = uniqid("Application");
        $appStorage = $integrationStorage->getSubStorage($appName);

        $sharpRepositoryRoot = realpath(__DIR__. "/../..");

        $appStorage->write("composer.json", Terminal::stringToFile(
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

        return $appStorage;
    }
}