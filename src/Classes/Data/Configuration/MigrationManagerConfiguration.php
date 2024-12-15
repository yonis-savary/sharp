<?php

namespace YonisSavary\Sharp\Classes\Data\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class MigrationManagerConfiguration
{
    use ConfigurationElement;

    /**
     * @param string $tableName Name of your database's table responsible of holding migration informations
     * @param string $directoryName Name of the subdirectory in your application directory that holds migrations
     */
    public function __construct(
        public readonly string $tableName = "__sharp_app_migrations",
        public readonly string $directoryName = "Migrations"
    ){}
}