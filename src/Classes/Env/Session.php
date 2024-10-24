<?php

namespace YonisSavary\Sharp\Classes\Env;

use Exception;
use RuntimeException;
use YonisSavary\Sharp\Classes\Core\AbstractMap;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Core\Autoloader;

class Session extends AbstractMap
{
    use Component;

    public function __construct(string $sessionName=null)
    {
        $sessionName ??= md5(Autoloader::projectRoot());
        $status = session_status();

        if ($status === PHP_SESSION_DISABLED)
            throw new Exception('Cannot use Session when sessions are disabled !');

        if ($status === PHP_SESSION_NONE)
        {
            // Setting the session_name has two big advantages to it !
            // - Avoid sessions collision between two apps that are on different ports of the same host
            // - PHP Still clear session files (which is disabled if a custom session path is used)
            // This way, two applications that don't have the same root will have different sessions
            session_name($sessionName);

            if (!session_start())
                throw new RuntimeException('Cannot start session !');
        }

        $this->storage = &$_SESSION;
    }
}