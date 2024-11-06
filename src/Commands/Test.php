<?php

namespace YonisSavary\Sharp\Commands;

use Throwable;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class Test extends AbstractCommand
{
    public function getHelp(): string
    {
        return 'Execute every PHPUnit installation/test suites';
    }

    protected function executeInDir(callable $callback, string $directory)
    {
        $original = getcwd();
        chdir($directory);
        $callback();
        chdir($original);
    }

    public function __invoke(Args $args)
    {
        $toTest = Configuration::getInstance()->toArray('applications');

        array_unshift($toTest, Autoloader::projectRoot());

        if ($sharpSrc = $GLOBALS["sharp-src"] ?? false)
            array_unshift($toTest, realpath("$sharpSrc/.."));
        else
            array_unshift($toTest, Utils::relativePath("vendor/yonis-savary/sharp/src"));

        try
        {
            $output = shell_exec("phpunit --version");
            $useBinaryDirectory = (!str_starts_with($output, "PHPUnit"));
        }
        catch (Throwable $_)
        {
            $useBinaryDirectory = true;
        }

        $this->log("Testing ".count($toTest)." directories " .
            ($useBinaryDirectory ?
                '(Using composer phpunit binary)':
                '(Using "phpunit" path command)'
            )
        );

        $gotAnyError = false;
        foreach ($toTest as $application)
        {
            $phpunit = Utils::joinPath($application, 'vendor/bin/phpunit');

            $applicationRelativePath = str_replace(Autoloader::projectRoot(), "", $application);
            if (!$applicationRelativePath) $applicationRelativePath = ".";

            if (!is_file($phpunit))
            {
                $this->log($this->withYellowColor("•") . " No test found in [$applicationRelativePath]");
                continue;
            }

            $this->executeInDir(function() use ($application, $applicationRelativePath, $useBinaryDirectory, &$gotAnyError) {

                $start = hrtime(true);

                $command = "phpunit";
                if ($useBinaryDirectory)
                {
                    $command = str_starts_with(PHP_OS, 'WIN') ?
                        '.\\vendor\\bin\\phpunit':
                        './vendor/bin/phpunit';
                }


                $output = shell_exec("$command --colors=never --display-warnings") ?? '';
                $duration = hrtime(true) - $start;

                $durationMilliseconds = $duration/1_000_000;

                $lines = array_filter(explode("\n", $output));

                $lastLine = end($lines);

                if (str_starts_with($lastLine, 'OK'))
                {
                    $this->log($this->withGreenColor("✓") . " OK ($applicationRelativePath, " . substr($lastLine, 4) ." in $durationMilliseconds ms");
                }
                else
                {
                    $gotAnyError = true;
                    $this->log($this->withRedColor("✗") . "  Errors/Warnings while testing [$application]");
                    $this->log($output);
                }

            }, $application);

        }

        return (int) $gotAnyError;
    }
}