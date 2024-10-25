<?php

namespace YonisSavary\Sharp\Classes\Extras;

use Exception;
use InvalidArgumentException;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Configurable;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Extras\AutobahnDrivers\BaseDriver;
use YonisSavary\Sharp\Classes\Extras\AutobahnDrivers\DriverInterface;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Core\Utils;
use YonisSavary\Sharp\Classes\Http\Request;

class Autobahn
{
    use Component, Configurable;

    protected ?Database $database = null;
    protected DriverInterface $driver;

    public static function getDefaultConfiguration(): array
    {
        return ['driver' => BaseDriver::class];
    }

    public function __construct(Database $database=null)
    {
        $this->loadConfiguration();
        $this->database = $database ?? Database::getInstance();

        $driver = $this->configuration['driver'];

        if (!Utils::implements($driver, DriverInterface::class))
            throw new Exception('Autobahn driver must implements '. DriverInterface::class);

        $this->driver = new $driver($this->database);
    }

    public function throwOnInvalidModel(string $model): AbstractModel|string
    {
        if (!Utils::extends($model, AbstractModel::class))
            throw new InvalidArgumentException("[$model] does not use the ". AbstractModel::class ." class !");

        return $model;
    }

    /**
     * @return array<Route>
     */
    public function all(
        string $model,
        array $createMiddlewares=[],
        array $createMultiplesMiddlewares=[],
        array $readMiddlewares=[],
        array $updateMiddlewares=[],
        array $deleteMiddlewares=[]
    ): array
    {
        return [
            $this->create($model, ...$createMiddlewares),
            $this->createMultiples($model, ...$createMultiplesMiddlewares),
            $this->read($model, ...$readMiddlewares),
            $this->update($model, ...$updateMiddlewares),
            $this->delete($model, ...$deleteMiddlewares),
        ];
    }

    /**
     * @return array[\Sharp\Classes\Data\Model,array]
     */
    protected function getNewRouteExtras(string $model, callable ...$middlewares): array
    {
        return ['autobahn-model' => $model, 'autobahn-middlewares' => $middlewares];
    }

    protected function addRoute(
        string $model,
        array $middlewares,
        callable $callback,
        array $methods,
        string $suffix=''
    ): Route
    {
        $routeExtras = $this->getNewRouteExtras($model, ...$middlewares);
        $model = $this->throwOnInvalidModel($model);

        return new Route(
            $model::getTable() . $suffix,
            $callback,
            $methods,
            [],
            $routeExtras
        );
    }

    public function create(string $model, callable ...$middlewares): Route
    {
        return $this->addRoute($model, $middlewares, fn(Request $request) => $this->driver->createCallback($request), ['POST']);
    }

    public function createMultiples(string $model, callable ...$middlewares): Route
    {
        return $this->addRoute($model, $middlewares, fn(Request $request) => $this->driver->multipleCreateCallback($request), ['POST'], '/create-multiples');
    }

    public function read(string $model, callable ...$middlewares): Route
    {
        return $this->addRoute($model, $middlewares, fn(Request $request) => $this->driver->readCallback($request), ['GET']);
    }

    public function update(string $model, callable ...$middlewares): Route
    {
        return $this->addRoute($model, $middlewares, fn(Request $request) => $this->driver->updateCallback($request), ['PUT', 'PATCH']);
    }

    public function delete(string $model, callable ...$middlewares): Route
    {
        return $this->addRoute($model, $middlewares, fn(Request $request) => $this->driver->deleteCallback($request), ['DELETE']);
    }
}