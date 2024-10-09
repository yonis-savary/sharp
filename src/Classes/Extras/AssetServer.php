<?php

namespace YonisSavary\Sharp\Classes\Extras;

use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Classes\Env\Cache;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Classes\Web\Router;
use YonisSavary\Sharp\Core\Autoloader;

class AssetServer
{
    use Component, Configurable;

    const EXTENSIONS_MIMES = [
        "js" => "application/javascript",
        "css" => "text/css"
    ];

    protected $cacheIndex = [];

    public static function getDefaultConfiguration(): array
    {
        return [
            "enabled"     => true,
            "cached"      => true,
            "url"         => "/assets",
            "middlewares" => [],
            "max-age"     => false
        ];
    }

    public function __construct()
    {
        $this->loadConfiguration();

        if (!$this->isEnabled())
            return;

        if ($this->isCached())
            $this->cacheIndex = &Cache::getInstance()->getReference("sharp.asset-server");

        $req = Request::buildFromGlobals();
        $this->handleRequest($req);
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

        foreach (Autoloader::getListFiles(Autoloader::ASSETS) as $file)
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
        $encodedAssetName = urlencode($assetName);
        $routePath = $this->configuration["url"];
        return "$routePath?file=$encodedAssetName";
    }

    public function handleRequest(Request $req, bool $returnResponse=false): Response|false
    {
        $routePath = $this->configuration["url"];
        $middlewares = $this->configuration["middlewares"];
        $selfRoute = Route::get($routePath, fn($req) => $this->serve($req), $middlewares);

        if (!$selfRoute->match($req))
            return false;

        $response = $selfRoute($req);

        if ($returnResponse)
            return $response;

        $response->display();
        die;
    }

    protected function serve(Request $req): Response
    {
        if (!$searchedFile = ($req->params("file") ?? false))
            return Response::json("A 'file' parameter is needed", 401);

        if (!$path = $this->findAsset($searchedFile))
            return Response::json("Asset [$searchedFile] not found", 404);

        $res = Response::file($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($cacheTime = $this->configuration["max-age"])
            $res->withHeaders(["Cache-Control" => "max-age=$cacheTime"]);

        if ($mime = self::EXTENSIONS_MIMES[$extension] ?? false)
            $res->withHeaders(["Content-Type" => $mime]);

        return $res;
    }
}