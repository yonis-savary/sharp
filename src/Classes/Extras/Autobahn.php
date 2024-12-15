<?php

namespace YonisSavary\Sharp\Classes\Extras;

use InvalidArgumentException;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Data\AbstractModel;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Extras\AutobahnDrivers\DriverInterface;
use YonisSavary\Sharp\Classes\Extras\Configuration\AutobahnConfiguration;
use YonisSavary\Sharp\Classes\Web\Route;
use YonisSavary\Sharp\Core\Utils;

class Autobahn
{
    use Component;

    protected ?Database $database = null;
    protected DriverInterface $driver;

    public function __construct(Database $database=null, AutobahnConfiguration $configuration=null)
    {
        $configuration ??= AutobahnConfiguration::resolve();
        $this->database = $database ?? Database::getInstance();
        $this->driver = $configuration->driver;
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

    protected function getRoute(
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
        return $this->getRoute($model, $middlewares, [$this->driver::class, "createCallback"], ['POST']);
    }

    public function createMultiples(string $model, callable ...$middlewares): Route
    {
        return $this->getRoute($model, $middlewares, [$this->driver::class, "multipleCreateCallback"], ['POST'], '/create-multiples');
    }

    public function read(string $model, callable ...$middlewares): Route
    {
        return $this->getRoute($model, $middlewares, [$this->driver::class, "readCallback"], ['GET']);
    }

    public function update(string $model, callable ...$middlewares): Route
    {
        return $this->getRoute($model, $middlewares, [$this->driver::class, "updateCallback"], ['PUT', 'PATCH']);
    }

    public function delete(string $model, callable ...$middlewares): Route
    {
        return $this->getRoute($model, $middlewares, [$this->driver::class, "deleteCallback"], ['DELETE']);
    }
}