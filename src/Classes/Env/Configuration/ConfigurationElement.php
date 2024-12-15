<?php

namespace YonisSavary\Sharp\Classes\Env\Configuration;

use Throwable;

trait ConfigurationElement
{
    /**
     * @return static New instance of the configuration element from the app configuration (or a default one)
     */
    public static function resolve()
    {
        $self = get_called_class();

        try
        {
            $default = new $self();
        }
        catch (Throwable $_)
        {
            $default = null;
        }

        return Configuration::getInstance()->resolve($self, $default);
    }
}