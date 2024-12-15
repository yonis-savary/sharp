<?php

namespace YonisSavary\Sharp\Classes\CLI;

use YonisSavary\Sharp\Core\Configuration\ApplicationsToLoad;

class Terminal
{
    /**
     * Simply an alias to `readline()`
     */
    public static function prompt(string $question): ?string
    {
        $output = readline($question);
        if ($output === "")
            $output = null;

        return $output;
    }

    public static function confirm(string $question): bool
    {
        $str = readline($question . ' [n] (y/n) : ');
        $str = strtolower($str);

        return $str === 'y' || $str === 'yes';
    }

    /**
     * Display a list to the user and ask to choose an item
     * @param array $choices Choices for the user
     * @param string $question Prompt for the user
     * @return mixed Selected option (the value, not index)
     */
    public static function promptList(array $choices, string $question, bool $returnIndex=false): mixed
    {
        print("$question\n");
        for ($i=0; $i<count($choices); $i++)
            printf(" %s - %s\n", $i+1, $choices[$i]);

        $index = (int) (self::prompt("\n> "));

        if ($returnIndex)
            return $index;

        return $choices[$index-1] ?? null;
    }

    /**
     * Make the user choose between enabled applications
     * @note If only one application is enabled, it is chosen by default
     * @return string Chosen App relative path (as written in configuration)
     */
    public static function chooseApplication(): string
    {
        $applications = ApplicationsToLoad::resolve()->applications;

        if (count($applications) === 1)
            return $applications[0];

        return self::promptList(
            $applications,
            'This command needs you to select an application'
        );
    }

    /**
     * Util function to write a string and remove excessive tabs before lines
     */
    public static function stringToFile(string $content, int $d=3): string
    {
        return preg_replace('/^( {4}){'.$d."}|(^ +\n?$)/m", '', $content);
    }
}