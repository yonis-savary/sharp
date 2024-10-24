<?php

namespace YonisSavary\Sharp\Classes\Security;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Classes\Env\Session;
use YonisSavary\Sharp\Classes\Http\Request;

class Csrf
{
    use Component, Configurable;

    const CACHE_KEY = 'sharp.security.csrf.token';

    protected Session $session;

    public static function getDefaultConfiguration(): array
    {
        return [
            'html-input-name' => 'csrf-token'
        ];
    }

    public function __construct(Session $session)
    {
        $this->loadConfiguration();
        $this->session = $session ?? Session::getInstance();
    }

    public function getHTMLInput(): string
    {
        $token = $this->getToken();
        $inputName = $this->configuration['html-input-name'];

        return "<input type='hidden' name='$inputName' value='$token'>";
    }

    public function getToken(): string
    {
        if ($token = $this->session->try(self::CACHE_KEY))
            return $token;

        $newToken = bin2hex(random_bytes(32)); // 64 HEX String
        $this->session->set(self::CACHE_KEY, $newToken);

        return $newToken;
    }

    public function resetToken(): void
    {
        $this->session->unset(self::CACHE_KEY);
    }

    /**
     * Check if the given request contain a valid CSRF token
     * @return bool `true` on valid token, `false` otherwise
     */
    public function checkRequest(Request $request): bool
    {
        $inputName = $this->configuration['html-input-name'];
        $requestToken = $request->params($inputName);
        $validToken = $this->getToken();

        if (!$requestToken)
            return false;

        return hash_equals(
            crypt($requestToken, 'dummySalt'),
            crypt($validToken, 'dummySalt')
        );
    }
}


