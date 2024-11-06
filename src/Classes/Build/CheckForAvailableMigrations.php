<?php

namespace YonisSavary\Sharp\Classes\Build;

use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\Data\MigrationManager;

class CheckForAvailableMigrations extends AbstractBuildTask
{
    public function execute(): int
    {
        $manager = MigrationManager::getInstance();

        $availables = $manager->listAllMigrations();
        $done = $manager->listDoneMigrations();

        $todo = array_diff($availables, $done);

        if (count($todo))
        {
            $this->log(count($todo) . " migrations are waiting to be launched");
            $this->log("If you want to apply them, please execute the migration-launch command");
            $this->log("");
            foreach ($todo as $migrationName)
                $this->log("- $migrationName");

            return 1;
        }

        return 0;
    }

    public function getWatchList(): array
    {
        return [
            MigrationManager::getInstance()->getStorage()->getRoot()
        ];
    }
}