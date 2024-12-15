<?php

use YonisSavary\Sharp\Classes\Data\Configuration\DatabaseConfiguration;
use YonisSavary\Sharp\Classes\Extras\Configuration\AssetServerConfiguration;
use YonisSavary\Sharp\Classes\Security\Configuration\AuthenticationConfiguration;
use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;
use YonisSavary\Sharp\Tests\Root\TestApp\Models\TestUser;

return [
    new ApplicationsToLoad(
        "TestApp"
    ),

    new DatabaseConfiguration(
        "sqlite"
    ),

    new AuthenticationConfiguration(
        TestUser::class,
        "login",
        "password",
        "salt"
    ),

    new AssetServerConfiguration(
        nodePackages: [
            "bootstrap-icons"
        ]
    )
];