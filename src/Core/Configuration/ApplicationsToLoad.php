<?php


namespace YonisSavary\Sharp\Core\Configuration;

use Error;
use YonisSavary\Sharp\Classes\Env\Configuration\ConfigurationElement;
use YonisSavary\Sharp\Core\Utils;

class ApplicationsToLoad
{
    use ConfigurationElement;

    public function __construct(
        public string|array $applications=[]
    )
    {
        if (is_string($this->applications))
            $this->applications = [$this->applications];

        foreach ($this->applications as &$app)
        {
            if (!is_dir(Utils::relativePath($app)))
                throw new Error("Cannot load application $app (not a directory)");
        }
    }
}