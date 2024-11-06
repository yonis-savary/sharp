<?php

namespace YonisSavary\Sharp\Classes\CLI;

/**
 * Command classes can be executed through the CLI,
 * the only method you have to override is `__invoke()` and `getHelp()`
 */
abstract class AbstractCommand extends CLIUtils
{
    final public function getOrigin(): string
    {
        $class = get_called_class();
        $origin = preg_replace('/(\\\\Commands)?\\\\[^\\\\]+$/', '', $class);
        $origin = preg_replace('/.+\\\\/', '', $origin);
        $origin = preg_replace('/([a-z])([A-Z])/', "$1-$2", $origin);
        $origin = strtolower($origin);
        return $origin;
    }

    final public function getIdentifier(): string
    {
        return $this->getOrigin() . '@' . $this->getName();
    }

    final public function getName(): string
    {
        $class = get_called_class();
        $class = preg_replace('/.+\\\\/', '', $class);
        $class = preg_replace('/([a-z])([A-Z])/', "$1-$2", $class);
        $class = strtolower($class);
        return $class;
    }

    public function getHelp(): string
    {
        return '';
    }

    /**
     * This function is executed when the command is called
     * @return int A command exit code (0 = no error, 1..n = error code)
     */
    public abstract function execute(Args $args): int;

    /**
     * Directly execute a command without having to instanciate an object
     * @param string $argv Console parameters
     */
    final public static function call(string $argv='')
    {
        $class = get_called_class();
        return (new $class)->execute(new Args($argv));
    }
}