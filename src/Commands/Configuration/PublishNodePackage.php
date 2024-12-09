<?php

namespace YonisSavary\Sharp\Commands;

use YonisSavary\Sharp\Classes\CLI\AbstractCommand;
use YonisSavary\Sharp\Classes\CLI\Args;
use YonisSavary\Sharp\Classes\Env\Configuration;
use YonisSavary\Sharp\Classes\Extras\AssetServer;
use YonisSavary\Sharp\Core\Utils;

class PublishNodePackage extends AbstractCommand
{
    protected function addGlob(string $glob, array &$nodePackages)
    {
        $globPath = Utils::relativePath("node_modules/$glob");

        $this->log($globPath);

        if (!($files = glob($globPath)))
            return $this->log($this->withRedColor("Could not execute glob $globPath"));

        foreach ($files as $fileOrDirectory)
        {
            if (!is_dir($fileOrDirectory))
                return;

            $package = $fileOrDirectory;
            if (is_file(Utils::joinPath($package, "package.json")))
                return $this->addPackage($package, $nodePackages);

            foreach (Utils::listDirectories($package) as $subPackage)
                $this->addPackage($subPackage, $nodePackages);
        }
    }

    protected function addPackage(string $package, array &$nodePackages)
    {
        $nodePackages[] = basename($package);
    }

    public function execute(Args $args): int
    {
        $assetServer = AssetServer::getInstance();
        $configuration = $assetServer->getConfiguration();

        $configuration["node-packages"] = Utils::toArray($configuration["node-packages"] ?? []);
        $nodePackages = &$configuration["node-packages"];

        $initialCount = count($nodePackages);

        $packages = $args->values();
        foreach ($packages as $package)
        {
            if (str_contains($package, "*"))
                $this->addGlob($package, $nodePackages);
            else
                $this->addPackage($package, $nodePackages);
        }

        $nodePackages = array_values(array_unique($nodePackages));

        $config = Configuration::getInstance();
        $config->set(
            $assetServer->getConfigurationKey(),
            $configuration
        );
        $config->save();


        $this->log(
            $this->withGreenColor("Added " . (count($nodePackages)-$initialCount) . " packages to your asset server configuration")
        );

        return 0;
    }
}