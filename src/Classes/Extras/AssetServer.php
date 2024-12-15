<?php

namespace YonisSavary\Sharp\Classes\Extras;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Env\Configuration\JSONConfiguration;
use YonisSavary\Sharp\Classes\Extras\Configuration\AssetServerConfiguration;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Core\Autoloader;
use YonisSavary\Sharp\Core\Utils;

class AssetServer
{
    use Component;

    const EXTENSIONS_MIMES = [
        'js'   => 'application/javascript',
        'ejs'  => 'application/javascript',
        'mjs'  => 'application/javascript',
        'css'  => 'text/css',
        'scss' => 'text/css',
        'sass' => 'text/css',
        'json' => 'application/json',
    ];

    protected $cacheIndex = [];
    protected $nodeCacheIndex = [];
    protected AssetServerConfiguration $configuration;

    public function __construct(AssetServerConfiguration $configuration=null)
    {
        $this->configuration = $configuration ?? AssetServerConfiguration::resolve();

        if (!$this->configuration->enabled)
            return;

        if ($this->configuration->cached)
        {
            $cache = Cache::getInstance();
            $this->cacheIndex = &$cache->getReference('sharp.asset-server');
            $this->nodeCacheIndex = &$cache->getReference("sharp.asset-server-node");
        }

        foreach (Utils::toArray($this->configuration->nodePackages ?? []) as $package)
            $this->publishNodePackage($package);

        $req = Request::fromGlobals();
        $this->handleRequest($req);

    }

    public function publishNodePackage(string $nodePackage)
    {
        if ($files = ($this->nodeCacheIndex[$nodePackage] ?? []))
            return Autoloader::addToList(Autoloader::ASSETS, ...$files);

        $packagePath = Utils::relativePath("node_modules/$nodePackage");

        if (!is_dir($packagePath))
            return Logger::getInstance()->error("Could not publish $nodePackage ($packagePath is not a directory)");

        $packageConfig = new JSONConfiguration(Utils::joinPath($packagePath, "package.json"));

        $toAdd = [];
        foreach ($packageConfig->toArray("files") as $file)
        {
            $filePath = Utils::joinPath($packagePath, $file);

            if (is_dir($filePath))
                $toAdd[] = $filePath;
            else if ($subfiles = glob($filePath))
                array_push($toAdd, ...$subfiles);
        }

        $this->nodeCacheIndex[$nodePackage] = $toAdd;
        Autoloader::addToList(Autoloader::ASSETS, ...$toAdd);
    }


    /**
     * Find an asset absolute path from its path end
     *
     * @param string $assetName Requested asset name path's end
     * @return string|false Absolute asset's path or false if not found
     */
    public function findAsset(string $assetName): string|false
    {
        if ($path = $this->cacheIndex[$assetName] ?? false)
            return $path;

        if (!str_starts_with($assetName, "/"))
            $assetName = "/$assetName";

        foreach (Autoloader::getList(Autoloader::ASSETS) as $file)
        {
            if (!str_ends_with($file, $assetName))
                continue;

            return $this->cacheIndex[$assetName] = $file;
        }
        return false;
    }

    /**
     * @param string $assetName Requested asset name path's end
     * @return string An URL that will work with the assetServer internal route
     */
    public function getURL(string $assetName): string
    {
        return preg_replace("/\{.+?\}/", $assetName, $this->configuration->url);
    }

    public function handleRequest(Request $request, bool $returnResponse=false): Response|false
    {
        $routePath = $this->configuration->url;
        $middlewares = $this->configuration->middlewares;

        $selfRoute = Route::get($routePath, fn($_) => null, $middlewares);
        if (!$selfRoute->match($request))
            return false;

        $response = $this->serve($request);

        if ($returnResponse)
            return $response;

        $response->logSelf();
        $response->displayAndDie();
    }

    protected function serve(Request $req): Response
    {
        if (!$searchedFile = ($req->getSlug('filename') ?? false))
            return Response::json("A 'file' parameter is needed", 401);

        if (!$path = $this->findAsset($searchedFile))
            return Response::json("Asset [$searchedFile] not found", 404);

        $response = Response::file($path);

        if ($cacheTime = $this->configuration->maxAge)
        $response->withHeaders(['Cache-Control' => "max-age=$cacheTime"]);

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($mime = self::EXTENSIONS_MIMES[$extension] ?? false)
            $response->withHeaders(['Content-Type' => $mime]);

        return $response;
    }
}