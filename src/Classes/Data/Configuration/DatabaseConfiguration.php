<?php

namespace YonisSavary\Sharp\Classes\Data\Configuration;

use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;

class DatabaseConfiguration
{
    use ConfigurationElement;

    const DEFAULT_MYSQL_PORT = 3306;

    /**
     * @param string $driver PDO Driver to use
     * @param ?string $database Name of the database (or filename when using SQLite)
     * @param ?string $host Host name / url
     * @param ?int $port Service Port
     * @param ?string $user
     * @param ?string $password
     * @param string $charset Default charset to use
     */
    public function __construct(
        public readonly string $driver = 'mysql',
        public readonly ?string $database = 'database',
        public readonly ?string $host = 'localhost',
        public readonly ?int $port = self::DEFAULT_MYSQL_PORT,
        public readonly ?string $user = 'root',
        public readonly ?string $password = null,
        public readonly string $charset = 'utf8',
    ){}
}