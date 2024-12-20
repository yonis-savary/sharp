<?php

namespace YonisSavary\Sharp\Classes\Security;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Env\Session;
use YonisSavary\Sharp\Classes\Events\AuthenticatedUser;
use YonisSavary\Sharp\Classes\Security\Configuration\AuthenticationConfiguration;

class Authentication
{
    use Component;

    const ATTEMPTS_NUMBER     = 'failed-attempt-number';
    const SESSION_EXPIRE_TIME = 'session-expire-time';
    const USER_DATA           = 'user-data';
    const IS_LOGGED           = 'is-logged';

    protected AuthenticationConfiguration $configuration;

    public readonly string $model;
    public readonly string $loginField;
    public readonly string $passwordField;
    public readonly ?string $saltField;

    public readonly string $sessionNamespace;

    protected Session $session;
    protected Database $database;


    public function sessionKey(string $key)
    {
        return 'sharp.authentication.' . $this->sessionNamespace . '.' . $key;
    }

    protected function getSessionKey(string $key, mixed $defaultValue=null): mixed
    {
        return $this->session->get($this->sessionKey($key), $defaultValue);
    }

    protected function setSessionKey(string $key, mixed $value): void
    {
        $this->session->set($this->sessionKey($key), $value);
    }

    public function __construct(Session $session=null, AuthenticationConfiguration $configuration=null, Database $database=null)
    {
        $this->configuration = $configuration ?? AuthenticationConfiguration::resolve();

        $this->session = $session ?? Session::getInstance();
        $this->database = $database ?? Database::getInstance();

        $this->model         = $this->configuration->model;
        $this->loginField    = $this->configuration->loginField;
        $this->passwordField = $this->configuration->passwordField;
        $this->saltField     = $this->configuration->saltField;

        $this->sessionNamespace = md5($this->model . $this->loginField . $this->passwordField);

        if (!$this->isLogged())
            return;

        $expireTime = $this->getSessionKey(self::SESSION_EXPIRE_TIME);

        if (time() >= $expireTime)
            $this->logout();
        else
            $this->refreshExpireTime();
    }

    public function attempt(string $login,string $password): bool
    {
        /** @var AbstractModel */
        $model = $this->model;

        if (!($user = $model::select()->where($this->loginField, $login)->first($this->database)))
            return $this->failAttempt();

        $passwordField = $this->passwordField;
        $hash = $user->data->$passwordField;

        if ($saltField = $this->saltField)
            $password .= $user->data->$saltField;

        if (!password_verify($password, $hash))
            return $this->failAttempt();

        $this->login($user);

        return true;
    }

    /**
     * Directly login a user and set its data
     * @param array $userData Data of the user, can be retrieved with `getUser()`
     */
    public function login(array|AbstractModel $userData): void
    {
        if ($userData instanceof AbstractModel)
            $userData = $userData->toArray();

        $this->setSessionKey(self::IS_LOGGED, true);
        $this->setSessionKey(self::USER_DATA, $userData);
        $this->setSessionKey(self::ATTEMPTS_NUMBER, 0);

        $this->refreshExpireTime();

        EventListener::getInstance()->dispatch(new AuthenticatedUser(
            $userData,
            $this->model,
            $this->loginField,
            $this->passwordField,
            $this->saltField
        ));
    }

    protected function failAttempt(): bool
    {
        $this->logout();
        $this->session->edit(
            $this->sessionKey(self::ATTEMPTS_NUMBER),
            fn($x=0) => $x+1
        );

        return false;
    }

    protected function refreshExpireTime(): void
    {
        $sessionDuration = (int) $this->configuration->sessionDuration;
        $this->setSessionKey(self::SESSION_EXPIRE_TIME, time() + $sessionDuration);
    }

    public function logout(): void
    {
        $this->session->unset(
            $this->sessionKey(self::IS_LOGGED),
            $this->sessionKey(self::USER_DATA)
        );
    }

    public function isLogged(): bool
    {
        return (bool) $this->getSessionKey(self::IS_LOGGED, false);
    }

    public function attemptNumber(): int
    {
        return $this->getSessionKey(self::ATTEMPTS_NUMBER, 0);
    }

    public function getUser(): ?array
    {
        return $this->getSessionKey(self::USER_DATA);
    }

    /**
     * @return mixed `false` if not logged in, the id or other primary key value, `null` on failure
     */
    public function getUserId(): mixed
    {
        if (!$this->isLogged())
            return false;

        /** @var AbstractModel $model */
        $model = $this->configuration->model;
        $primaryKey = $model::getPrimaryKey();

        $data = $this->getUser();
        return $data["data"][$primaryKey] ?? null;
    }
}