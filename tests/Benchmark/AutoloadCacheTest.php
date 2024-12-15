<?php

namespace YonisSavary\Sharp\Tests\Benchmark;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;
use YonisSavary\Sharp\Tests\Integration\IntegrationAppFactory;

class AutoloadCacheTest extends TestCase
{
    public function test_cacheSaveTime()
    {
        $appStorage = IntegrationAppFactory::createPlainSharpApp();

        $nullRedirect = " > /dev/null 2>&1";

        $originalDir = getcwd();
        chdir($appStorage->getRoot());

        shell_exec("composer exec sharp-install $nullRedirect");
        shell_exec("php do create-application AppName --add-autoload $nullRedirect");

        file_put_contents(
            $appStorage->path("sharp.php"),
            Terminal::stringToFile(
            "<?php

            return [
                new ".ApplicationsToLoad::class."([\"AppName\"])
            ];
        "));

        for ($i=0; $i<=1000; $i++)
        {
            $appClassname = uniqid("BuildTask");
            # Assets that Helpers files are loaded
            $appStorage->write(
                "AppName/Classes/$appClassname.php",
                Terminal::stringToFile(
                "<?php

                namespace AppName\Classes;

                use ". AbstractBuildTask::class .";

                class $appClassname extends AbstractBuildTask
                {
                    public function execute(): int { return 0; }
                }
                ", 4)
            );
        }

        $appStorage->write(
            "AppName/Commands/CountBuildTasks.php",
            Terminal::stringToFile(
            "<?php

            namespace AppName\Commands;

            use ". AbstractCommand::class .";
            use ". Args::class .";
            use ". Autoloader::class .";
            use ". AbstractBuildTask::class .";

            class CountBuildTasks extends AbstractCommand
            {
                public function execute(Args \$args): int
                {
                    \$tasks = AutoLoader::classesThatExtends(AbstractBuildTask::class);
                    echo count(\$tasks);
                    return 0;
                }
            }
            ")
        );

        $logger = Logger::getInstance();

        $firstStart = hrtime(true);
        $output = shell_exec("php do count-build-tasks --command-output-only");
        $firstTime = (hrtime(true) - $firstStart) / 1000000;
        $this->assertGreaterThan(1000, $output);

        shell_exec("php do cache-autoload $nullRedirect");
        shell_exec("php do count-build-tasks --command-output-only $nullRedirect");

        $secondStart = hrtime(true);
        $output = shell_exec("php do count-build-tasks --command-output-only");
        $secondTime = (hrtime(true) - $secondStart) / 1000000;
        $this->assertLessThan($firstTime, $secondTime);

        $logger->info("Autoload Benchmark (Load 1000 build tasks, before caching) : $firstTime ms");
        $logger->info("Autoload Benchmark (Load 1000 build tasks, before caching) : $secondTime ms");

        chdir($originalDir);
        shell_exec("rm -r " . $appStorage->getRoot() . " $nullRedirect");
    }
}