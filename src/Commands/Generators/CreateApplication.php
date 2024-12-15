<?php

namespace YonisSavary\Sharp\Commands\Generators;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Env\Configuration\JSONConfiguration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class CreateApplication extends AbstractCommand
{
    public function createApplication(string $appName)
    {
        if (!preg_match("/^(\/?[A-Z][a-zA-Z0-9]*)+$/", $appName))
            return $this->log("Given app name must be made of PascalName words (can be separated by '/')");

        $appDirectory = Utils::relativePath($appName);

        if (is_dir($appDirectory))
        {
            $this->log("[$appName] already exists");
            return 1;
        }

        $this->log("Making [$appName]");
        mkdir($appName, recursive:true);
        return 0;
    }

    public function execute(Args $args): int
    {
        $values = $args->values();

        if (!count($values))
            $values = [Terminal::prompt('App name (PascalCase): ')];

        $gotError = false;
        foreach($values as $app)
            $gotError |= $this->createApplication($app);

        $this->log($this->withYellowBackground("Do not forget to enable this application in sharp.php"));

        if (is_file(Utils::relativePath("composer.json")))
        {
            $addToPSR4 = $args->isPresent("a", "add-autoload") || Terminal::confirm("Add applications to composer file ? ");

            if ($addToPSR4)
            {
                $composerFile = new JSONConfiguration(Utils::relativePath("composer.json"));

                $this->log("Adding " . count($values) . " entries to composer.json autoload");
                $composerFile->edit("autoload", function(array $autoload) use ($values) {
                    $autoload["psr-4"] ??= [];
                    foreach ($values as $app)
                        $autoload["psr-4"]["$app\\"] = $app;

                    return $autoload;
                }, []);

                $this->log("Saving composer.json");
                $composerFile->save();

                $this->log("Performing dump-autoload");
                $this->shellInDirectory("composer dump-autoload", Autoloader::projectRoot());
            }
        }

        return (int) $gotError;
    }

    public function getHelp(): string
    {
        return 'Create an application directory and add it to your configuration';
    }
}