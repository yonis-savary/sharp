<?php

use YonisSavary\Sharp\Classes\Core\Logger;

/**
 * Shortcut to `Logger::getInstance()->debug()`
 * @param mixed ...$messages Informations/Data to log
 */
function debug(mixed $message, array $context=[])
{
    Logger::getInstance()->debug($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->info()`
 * @param mixed ...$messages Informations/Data to log
 */
function info(mixed $message, array $context=[])
{
    Logger::getInstance()->info($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->notice()`
 * @param mixed ...$messages Informations/Data to log
 */
function notice(mixed $message, array $context=[])
{
    Logger::getInstance()->notice($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->warning()`
 * @param mixed ...$messages Informations/Data to log
 */
function warning(mixed $message, array $context=[])
{
    Logger::getInstance()->warning($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->error()`
 * @param mixed ...$messages Informations/Data to log
 */
function error(mixed $message, array $context=[])
{
    Logger::getInstance()->error($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->critical()`
 * @param mixed ...$messages Informations/Data to log
 */
function critical(mixed $message, array $context=[])
{
    Logger::getInstance()->critical($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->alert()`
 * @param mixed ...$messages Informations/Data to log
 */
function alert(mixed $message, array $context=[])
{
    Logger::getInstance()->alert($message, $context);
}

/**
 * Shortcut to `Logger::getInstance()->emergency()`
 * @param mixed ...$messages Informations/Data to log
 */
function emergency(mixed $message, array $context=[])
{
    Logger::getInstance()->emergency($message, $context);
}
