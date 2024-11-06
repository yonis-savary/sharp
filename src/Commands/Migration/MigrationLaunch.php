<?php

namespace YonisSavary\Sharp\Commands\Migration;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class MigrationLaunch extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Execute all missing migrations from your database";
    }

    public function execute(Args $args): int
    {
        $manager = MigrationManager::getInstance();
        $logger = Logger::getInstance();

        $migrationSuccess = null;

        if ($name = $args->values()[0] ?? false)
        {
            $this->log("Executing migration [$name]");
            $migrationSuccess = $manager->executeMigration($name);
        }
        else
        {
            $this->log("Executing all available migrations");
            $migrationSuccess = $manager->executeAllMigrations();
        }

        if ($migrationSuccess)
        {
            $this->log($this->withGreenColor("Migration applied successfuly !"));
            return 0;
        }
        else
        {
            $error = $manager->getLastError();
            $this->log($this->withRedColor("Error while executing ".$manager->getLastErrorFile()." content !"));
            $this->log($this->withRedColor($error->getMessage() . ". Please see your logs for more"));
            $logger->error($error);
            return 1;
        }
    }
}