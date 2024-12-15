<?php

namespace YonisSavary\Sharp\Tests\Integration;

use PHPUnit\Framework\TestCase;
use YonisSavary\Sharp\Classes\CLI\AbstractBuildTask;
use YonisSavary\Sharp\Classes\CLI\Terminal;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;

class CommandTests extends TestCase
{
    public function test_appCreation()
    {
        if (!str_contains(strtolower(PHP_OS), "linux"))
        {
            Logger::getInstance()->info("Cannot perform integration test on non-linux system");
            $this->assertTrue(true);
            return 0;
        }

        $appStorage = IntegrationAppFactory::createPlainSharpApp();

        $nullRedirect = " > /dev/null 2>&1";

        $originalDir = getcwd();
        chdir($appStorage->getRoot());

        shell_exec("composer exec sharp-install $nullRedirect");

        file_put_contents(
            $appStorage->path("sharp.php"),
            Terminal::stringToFile(
            "<?php
            return [];
        "));

        shell_exec("php do create-application AppName -a $nullRedirect");
        $this->assertTrue($appStorage->isDirectory("AppName"));

        file_put_contents(
            $appStorage->path("sharp.php"),
            Terminal::stringToFile(
            "<?php

            return [
                new ".ApplicationsToLoad::class."([\"AppName\"])
            ];
        "));

        shell_exec("php do create-cache TestCache");
        $this->assertTrue($appStorage->isFile("AppName/Classes/Caches/TestCache.php"));

        shell_exec("php do create-controller TestController");
        $this->assertTrue($appStorage->isFile("AppName/Controllers/TestController.php"));

        shell_exec("php do create-map TestMap");
        $this->assertTrue($appStorage->isFile("AppName/Classes/Maps/TestMap.php"));

        shell_exec("php do create-middleware TestMiddleware");
        $this->assertTrue($appStorage->isFile("AppName/Middlewares/TestMiddleware.php"));

        shell_exec("php do create-storage TestStorage");
        $this->assertTrue($appStorage->isFile("AppName/Classes/Storages/TestStorage.php"));

        shell_exec("php do create-straw TestStraw");
        $this->assertTrue($appStorage->isFile("AppName/Classes/Straws/TestStraw.php"));





        # Assets that Helpers files are loaded
        $appStorage->write(
            "AppName/Helpers/write-a-file.php",
            "<?php storeWrite('helper-file', 'hello');"
        );
        shell_exec("php do $nullRedirect");
        $this->assertEquals("hello", $appStorage->read("Storage/helper-file"));






        # Assets that Build task is executed
        $appStorage->write(
        "AppName/Classes/BuildFileWriter.php",
        Terminal::stringToFile(
        "<?php

        namespace AppName\\Classes\\BuildTask;

        use ".AbstractBuildTask::class.";

        class BuildFileWriter extends AbstractBuildTask
        {
            public function execute(): int
            {
                storeWrite('build-output', 'madebybuild');
                return 0;
            }
        }
        ", 2)
        );
        shell_exec("php do build --ignore-tests $nullRedirect");
        $this->assertEquals("madebybuild", $appStorage->read("Storage/build-output"));


        chdir($originalDir);
        shell_exec("rm -r " . $appStorage->getRoot() . " $nullRedirect");
    }

    /*
    public function test_twoAppCanLoad()
    {
        if (!str_contains(strtolower(PHP_OS), "linux"))
        {
            Logger::getInstance()->info("Cannot perform integration test on non-linux system");
            $this->assertTrue(true);
            return 0;
        }

        $appStorage = IntegrationAppFactory::createPlainSharpApp();

        $nullRedirect = " > /dev/null 2>&1";

        $originalDir = getcwd();
        chdir($appStorage->getRoot());

        shell_exec("composer install $nullRedirect");
        shell_exec("composer exec sharp-install $nullRedirect");

        shell_exec("php do create-application AppOne $nullRedirect");
        $this->assertTrue($appStorage->isDirectory("AppOne"));

        shell_exec("php do create-application AppTwo $nullRedirect");
        $this->assertTrue($appStorage->isDirectory("AppTwo"));



        # Assets that Helpers files are loaded
        $appStorage->write("AppOne/Helpers/write-a-file.php", "<?php storeWrite('file-one', 'fromappone');");
        $appStorage->write("AppTwo/Helpers/write-a-file.php", "<?php storeWrite('file-two', 'fromapptwo');");
        shell_exec("php do");
        $this->assertEquals("fromappone", $appStorage->read("Storage/file-one"));
        $this->assertEquals("fromapptwo", $appStorage->read("Storage/file-two"));

        chdir($originalDir);
        shell_exec("rm -r " . $appStorage->getRoot());
    }
        */
}