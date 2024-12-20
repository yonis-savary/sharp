<?php

namespace YonisSavary\Sharp\Classes\Env\Configuration;

use Exception;
use RuntimeException;
use YonisSavary\Sharp\Classes\Core\GenericMap;
use YonisSavary\Sharp\Core\Utils;

class JSONConfiguration extends GenericMap
{
    protected ?string $filename = null;
    protected bool $merged = false;

    /**
     * @param string $filename `null` if the config is only an object, a relative path if it must be saved
     */
    public function __construct(string $filename=null)
    {
        if (!$filename)
            return;

        if (!is_file($filename))
            $filename = Utils::relativePath($filename);

        $this->storage = [];
        $this->filename = $filename;

        // Info: this verification comes after the previous assignment
        // because we can create a config from nothing then save it in a file

        if (!is_file($filename))
            return;

        $json = file_get_contents($filename);
        $this->storage = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Create a new Configuration object from an array(assoc) of data
     */
    public static function fromArray(array $data): self
    {
        $config = new self(null);
        $config->merge($data);

        return $config;
    }

    /**
     * @param string $path This parameter can be used as a 'Save As...' feature to copy a configuration, if `null`, the current path is used
     */
    public function save(string $path=null): void
    {
        if ($this->merged)
            throw new RuntimeException('Cannot save a configuration that comes from multiples files');

        $path ??= $this->filename;

        if (!$path)
            throw new Exception("Couldn't save a configuration without a file name !");

        file_put_contents($path, json_encode($this->storage, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    public function mergeWithFile(string $path, bool $throwOnError=true): self
    {
        $filePath = Utils::relativePath($path);

        if (!file_exists($path))
        {
            if ($throwOnError)
                throw new RuntimeException($path);

            return $this;
        }

        $json = file_get_contents($filePath);
        $object = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $this->merge($object);

        $this->merged = true;

        return $this;
    }
}