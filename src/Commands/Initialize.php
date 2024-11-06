<?php

namespace YonisSavary\Sharp\Commands;

use Throwable;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Commands\Cache\CacheEnable;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class Initialize extends AbstractCommand
{
    public function execute(Args $args): int
    {
        $config = Configuration::getInstance();
        if ($config->has("applications"))
        {
            $this->log( $this->withYellowColor("It seems that an application already exists in your configuration"));
            $this->log( $this->withYellowColor("Cannot re-initialize your project"));
            $this->log( $this->withYellowColor("Exiting."));
            return 1;
        }

        $this->log($this->withGreenColor("Thanks you for using Sharp, you can find all documentation to get started at https://github.com/yonis-savary/sharp"));


        $this->log($this->withCyanBackground(" Step 1/4: Application creation "));

        $appName = null;
        do
        {
            if ($appName)
                $this->log("Please use the PascalCase format for your application name");
            $appName = readline("Application Name (PascalCase) ? ");
        } while (!preg_match("/^[A-Z]+\w*$/", $appName));

        $appStorage = new Storage(Utils::relativePath($appName));
        $appStorage->makeDirectory("Models");
        $appStorage->write("Routes/app.php", Terminal::stringToFile(
        "<?php

        use ".Router::class.";
        use ".Route::class.";

        Router::getInstance()->addRoutes(
            Route::get('/', fn() => 'Hello !')
        );
        ", 2));
        $config->set("applications", [$appName]);
        $this->log("");




        $this->log($this->withCyanBackground(" Step 2/4: Database connection "));
        if (Terminal::confirm("Configure database connection ?"))
        {
            $dbDriver = strtolower(Terminal::prompt("PDO driver name [mysql] ? ") ?? "mysql");

            if ($dbDriver !== "sqlite")
            {
                $defaultPort = match($dbDriver) {
                    "postgresql" => 5432,
                    "oci" => 1521,
                    default => 3306
                };

                $dbHost = Terminal::prompt("Database host [127.0.0.1] ? ") ?? "127.0.0.1";
                $dbPort = Terminal::prompt("Database port [$defaultPort] ? ") ?? $defaultPort;
                $dbUser = Terminal::prompt("Database user [root] ? ") ?? "root";
                $dbName = Terminal::prompt("Database name ? ");
                $dbCharset = Terminal::prompt("Database charset [utf8] ? ") ?? "utf8";
                $dbPassword = null;
                $this->log($this->withYellowColor("Important : After this setup, please configure your password in sharp.json@database.password"));

                $config->set("database", [
                    'driver' => $dbDriver,
                    'database' => $dbName,
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'user' => $dbUser,
                    'password' => $dbPassword,
                    'charset' => $dbCharset,
                ]);
            }
            else
            {
                $dbName = Terminal::prompt("Database filename [database.sqlite] ? ") ?? "database.sqlite";
                $dbCharset = Terminal::prompt("Database charset [utf8] ? ") ?? "utf8";
                $config->set("database", [
                    'driver' => $dbDriver,
                    'database' => $dbName,
                    'charset' => $dbCharset,
                ]);
            }

        }
        $this->log("");



        $this->log($this->withCyanBackground(" Step 3/4: Testing "));
        if (Terminal::confirm("Add a test directory/config ?"))
        {

            $projectRoot = new Storage(Autoloader::projectRoot());

            $this->log("Writing phpunit.xml");
            $projectRoot->write("phpunit.xml", Terminal::stringToFile(
            "<phpunit bootstrap=\"vendor/autoload.php\">
                <testsuite name=\"$appName unit tests\">
                    <directory>$appName/Tests/Units</directory>
                </testsuite>
            </phpunit>
            "));


            $this->log("Making $appName/Tests/Units directory");
            $appStorage->makeDirectory("Tests/Units");

            try
            {
                shell_exec("phpunit --version");
                $this->log("Detected phpunit on your system, not requiring composer package");
            }
            catch (Throwable $_)
            {
                $this->log("Requiring phpunit package");
                if (!$projectRoot->isFile("vendor/bin/phpunit"))
                    $this->shellInDirectory("composer require phpunit/phpunit", $projectRoot->getRoot());
            }
        }
        $this->log("");

        $this->log($this->withCyanBackground(" Step 4/4: Caching "));
        if (Terminal::confirm("Enable differents caches ? (Can be toggled with php do cache-disable/enable)"))
        {
            CacheEnable::call();
        }
        $this->log("");


        $this->log($this->withBlueColor("Saving configuration"));
        $config->save();

        return 0;
    }
}