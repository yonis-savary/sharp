<?php

namespace YonisSavary\Sharp\Classes\Security;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Env\Session;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Security\Configuration\CsrfConfiguration;

class Csrf
{
    use Component;

    const CACHE_KEY = 'sharp.security.csrf.token';

    protected Session $session;

    protected CsrfConfiguration $configuration;

    public function __construct(Session $session, CsrfConfiguration $configuration=null)
    {
        $this->configuration = $configuration ?? CsrfConfiguration::resolve();
        $this->session = $session ?? Session::getInstance();
    }

    public function getHTMLInput(): string
    {
        $token = $this->getToken();
        $inputName = $this->configuration->htmlInputName;

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
        $inputName = $this->configuration->htmlInputName;
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


