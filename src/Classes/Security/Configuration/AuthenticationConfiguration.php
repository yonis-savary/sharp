<?php

namespace YonisSavary\Sharp\Classes\Security\Configuration;

use Exception;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;
use YonisSavary\Sharp\Core\Utils;

class AuthenticationConfiguration
{
    use ConfigurationElement;

    /**
     * @param string $model Classname of the used model (must extends AbstractModel)
     * @param array|string $loginField Field(s) use to identify the user (can be email, login, username...)
     * @param string $passwordField Field to check the password hash
     * @param ?string $saltField Salt field to use when authenticate (won't be used if `null`)
     * @param int $sessionDuration Number of second the used stays authenticated
     */
    public function __construct(
        public readonly string $model = 'App\Models\User',
        public readonly array|string $loginField = 'login',
        public readonly string $passwordField = 'password',
        public readonly ?string $saltField = null,
        public readonly int $sessionDuration = 3600
    ){
        if (!Utils::extends($model, AbstractModel::class))
            throw new Exception("$model does not extends the AbstractModel class");

        /** @var AbstractModel $model */

        $loginField = Utils::toArray($loginField);

        $fields = $model::getFieldNames();
        foreach ([...$loginField, $passwordField, $saltField] as $column)
        {
            if (!$column)
                continue;

            if (!in_array($column, $fields))
                throw new Exception("$model does not include the $column column");
        }
    }
}