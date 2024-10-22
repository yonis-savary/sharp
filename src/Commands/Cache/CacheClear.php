<?php

namespace YonisSavary\Sharp\Commands\Cache;

use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Classes\CacheElement;
use YonisSavary\Sharp\Classes\Env\Storage;
use YonisSavary\Sharp\Core\Utils;

class CacheClear extends AbstractCommand
{
    public function getHelp(): string
    {
        return "Delete files in Storage/Cache, use --all to delete permanent items";
    }

    protected function processFile(string $file, bool $deletePermanent)
    {
        if (!($cacheElement = CacheElement::fromFile($file)))
        {
            $this->log("Deleting $file");
            unlink($file);
            return;
        }

        $isPermanent = ($cacheElement->getTimeToLive() === Cache::PERMANENT);

        if ($isPermanent && (!$deletePermanent))
            return $this->log("Ignoring $file");

        $this->log("Deleting $file");
        unlink($file);
    }

    public function __invoke(Args $args)
    {
        $cache = Storage::getInstance()->getSubStorage("Cache");
        $deletePermanent = $args->isPresent("a", "all");

        foreach ($cache->exploreDirectory("/", Utils::ONLY_FILES) as $file)
            $this->processFile($file, $deletePermanent);
    }
}