<?php

namespace YonisSavary\Sharp\Classes\CLI;

use YonisSavary\Sharp\Classes\Core\Logger;

class CLIUtils
{
    /**
     * Display messages in the console only if in command line context
     */
    final public function log(string ...$mixed)
    {
        if (php_sapi_name() === 'cli')
            return print(join('', array_map(fn($x) => $x . "\n", $mixed)));

        foreach ($mixed as $element)
            Logger::getInstance()->info($element);
    }

    /**
     * Execute a shell command in a specified directory
     * @param string $command Command to execute
     * @param string $directory Target directory
     * @param bool $log If `true`, this command will display the command output
     */
    final protected function shellInDirectory(string $command, string $directory, bool $log=true): void
    {
        $this->executeInDirectory(function() use ($command, $log)
        {
            $proc = popen($command, 'r');
            if (!$log)
                return;

            while (!feof($proc))
                $this->log(fread($proc, 1024));

        }, $directory);
    }

    /**
     * Call your function while being in a directory
     * Then go back to the previous directory
     */
    final protected function executeInDirectory(callable $function, string $directory): void
    {
        $originalDirectory = getcwd();

        chdir($directory);
        $function();
        chdir($originalDirectory);
    }



    /**
     * Display a progressBar while going through an array
     * @param array $array Array to go through
     * @param callable $callback Callback to execute for each element (callback params are element, index and full array)
     * @note The callback can display info in the console, the progress bar shall adapt itself to it
     * @param int $progressBarSize size of the progress bar in the console
     * @param string $filledChar Char to use for the 'done' part of the progress bar
     * @param string $emptyChar to use for the 'remaining' part of the progress bar
     */
    protected function progressBar(array $array, callable $callback, int $progressBarSize=40, string $filledChar='█', string $emptyChar='░')
    {
        $log = str_contains(php_sapi_name(), 'cli') ?
            fn($text) => $this->log($text):
            fn($_) => null
        ;


        $log("\e7");

        $arraySize = count($array);
        for ($i=0; $i<$arraySize; $i++)
        {
            $iteration=$i+1;
            $progress = round(($i*$progressBarSize)/$arraySize);
            $remain = $progressBarSize - $progress;

            $log("\e8\e[0K");
            $log('['. str_repeat($filledChar, $progress) . str_repeat($emptyChar, $remain) ."] $iteration/$arraySize");

            ob_start();
            $callback($array[$i], $i, $array);
            $output = ob_get_clean();

            if ($output)
            {
                if (!str_ends_with($output, "\n"))
                    $output .= "\n";

                $log("\e8\e[0K" . $output . "\e7");
            }
        }
        $log("\e8\e[0K");
    }


    private function withColor(string $string, int $colorCode, bool $bold=false)
    {
        $boldStr = $bold ? ';1': '';
        return "\e[{$colorCode}{$boldStr}m" . $string . "\e[0m";
    }

    protected function withBlackColor        (string $string, bool $bold=true) { return $this->withColor($string, 30, $bold); }
    protected function withRedColor          (string $string, bool $bold=true) { return $this->withColor($string, 31, $bold); }
    protected function withGreenColor        (string $string, bool $bold=true) { return $this->withColor($string, 32, $bold); }
    protected function withYellowColor       (string $string, bool $bold=true) { return $this->withColor($string, 33, $bold); }
    protected function withBlueColor         (string $string, bool $bold=true) { return $this->withColor($string, 34, $bold); }
    protected function withMagentaColor      (string $string, bool $bold=true) { return $this->withColor($string, 35, $bold); }
    protected function withCyanColor         (string $string, bool $bold=true) { return $this->withColor($string, 36, $bold); }
    protected function withWhiteColor        (string $string, bool $bold=true) { return $this->withColor($string, 37, $bold); }
    protected function withDefaultColor      (string $string, bool $bold=true) { return $this->withColor($string, 39, $bold); }
    protected function withBlackBackground   (string $string) { return $this->withColor($string, 40); }
    protected function withRedBackground     (string $string) { return $this->withColor($string, 41); }
    protected function withGreenBackground   (string $string) { return $this->withColor($string, 42); }
    protected function withYellowBackground  (string $string) { return $this->withColor($string, 43); }
    protected function withBlueBackground    (string $string) { return $this->withColor($string, 44); }
    protected function withMagentaBackground (string $string) { return $this->withColor($string, 45); }
    protected function withCyanBackground    (string $string) { return $this->withColor($string, 46); }
    protected function withWhiteBackground   (string $string) { return $this->withColor($string, 47); }
    protected function withDefaultBackground (string $string) { return $this->withColor($string, 49); }
}