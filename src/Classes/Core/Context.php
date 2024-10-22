<?php

namespace YonisSavary\Sharp\Classes\Core;

use YonisSavary\Sharp\Core\Utils;

/**
 * Context is a static storage for every type of variable
 * This class can store one instance of every variable type
 *
 * It can be useful to retrieve the current request for example
 */
class Context
{
    private static array $context = [];

    /**
     * Register an object into the context
     * You can retrieve it with the `get` method
     */
    public static function set(mixed &$object)
    {
        $objectType = $object::class;
        self::$context[$objectType] = &$object;
    }

    /**
     * Specify a class name to forget a potential instance of it
     *
     * @return `true` is an instance was present, `false` otherwise
     */
    public static function forget(mixed $type): bool
    {
        if (!isset(self::$context[$type]))
            return false;

        unset(self::$context[$type]);
        return true;
    }

    /**
     * Retrieve a class instance from the context
     * If no instance is present, null shall be returned
     * If the specified type is a component type, the context will register the current component instance
     */
    public static function get(mixed $type, mixed $default=null): mixed
    {
        if (! $object = self::$context[$type] ?? false)
        {
            if (!Utils::uses($type, Component::class))
                return $default;

            $object = $type::getInstance();
            self::set($object);
        }

        return $object ?? $default;
    }
}