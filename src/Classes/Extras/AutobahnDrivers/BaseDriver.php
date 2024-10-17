<?php

namespace YonisSavary\Sharp\Classes\Extras\AutobahnDrivers;

use Exception;
use YonisSavary\Sharp\Classes\Core\EventListener;
use YonisSavary\Sharp\Classes\Data\Database;
use YonisSavary\Sharp\Classes\Data\ModelQuery;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Extras\Autobahn;
use YonisSavary\Sharp\Classes\Http\Classes\ResponseCodes;
use YonisSavary\Sharp\Classes\Http\Request;
use YonisSavary\Sharp\Classes\Http\Response;
use YonisSavary\Sharp\Core\Utils;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnCreateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnCreateBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnDeleteBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnMultipleCreateBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnReadAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnReadBefore;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateAfter;
use YonisSavary\Sharp\Classes\Events\AutobahnEvents\AutobahnUpdateBefore;
use YonisSavary\Sharp\Classes\Data\Model;
use Throwable;
use YonisSavary\Sharp\Classes\Data\AbstractModel;

class BaseDriver implements DriverInterface
{
    /**
     * Extract model name and middlewares from a route extras
     * @return array[AbstractModel,array]
     */
    protected static function extractRouteData(Request $request)
    {
        $extras = $request->getRoute()->getExtras();

        $model = $extras["autobahn-model"] ?? null;
        $model = Autobahn::getInstance()->throwOnInvalidModel($model);

        $middlewares = $extras["autobahn-middlewares"] ?? [];

        return [$model, $middlewares];
    }

    public static function createCallback(Request $request): Response
    {
        /** @var Model|string $model */
        list($model, $middlewares) = self::extractRouteData($request);

        $rows = $request->isJSON() ?
            $request->body():
            $request->post();

        if (Utils::isAssoc($rows))
            $rows = [$rows];

        $insertedIds = [];

        foreach ($rows as $row)
        {
            foreach ($middlewares as $middleware)
                $middleware($row);

            $fields = array_keys($row);
            $values = array_values($row);

            $events = EventListener::getInstance();
            $events->dispatch(new AutobahnCreateBefore($model, $fields, $values));

            $model::insertArray($row);

            $inserted = Database::getInstance()->lastInsertId();
            $insertedIds[] = $inserted;

            $events->dispatch(new AutobahnCreateAfter($model, $fields, $values, $inserted));
        }

        return Response::json(["insertedId"=>$insertedIds], ResponseCodes::CREATED);
    }




    public static function multipleCreateCallback(Request $request): Response
    {
        /** @var AbstractModel $model */
        list($model, $middlewares) = self::extractRouteData($request);

        $data = $request->body();

        if (!is_array($data))
            return Response::json('Only Arrays or objects are allowed !', 400);

        $data = Utils::toArray($data);

        $fields = array_keys($data[0]);
        $badFields = array_diff($fields, $model::getFieldNames()) ;
        if (count($badFields))
            return Response::json("[$model] does not contains theses fields " . json_encode($badFields), 400);

        $query = $model::insert($fields);

        $data = ObjectArray::fromArray($data);
        foreach ($middlewares as $middleware)
            $data = $data->filter($middleware);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnMultipleCreateBefore($data));

        $data->forEach(function($element) use (&$query) {
            $query->insertValues(array_values($element));
        });

        $query->fetch();
        $lastInsert = Database::getInstance()->lastInsertId();
        $insertedIdList = range($lastInsert-$data->length()+1, $lastInsert);

        $events->dispatch(new AutobahnMultipleCreateAfter((string) $model, $query, $insertedIdList));

        return Response::json(['insertedId' => $insertedIdList]);
    }




    public static function readCallback(Request $request): Response
    {
        /** @var AbstractModel $model */
        list($model, $middlewares) = self::extractRouteData($request);

        $doJoin = ($request->params("_join") ?? true) == true;


        if ($ignoresRaw = $request->params("_ignores"))
        {
            try
            {
                $ignoresRaw = json_decode($ignoresRaw, true, flags: JSON_THROW_ON_ERROR);
            }
            catch(Throwable $_) {}

            $ignores = Utils::toArray($ignoresRaw ?? []);
        }
        else
        {
            $ignores = [];
        }

        list($limit, $offset) = $request->list("_limit", "_offset");
        $request->unset(["_ignores", "_join", "_limit", "_offset"]);

        $query = new ModelQuery($model, ModelQuery::SELECT);
        $query->exploreModel($model, $doJoin, $ignores);

        if ($limit)
            $query->limit($limit);

        if ($offset)
            $query->offset($offset);

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnReadBefore((string) $model, $query));

        $results = $query->toObjectArray();

        $events->dispatch(new AutobahnReadAfter((string) $model, $query, $results));

        return Response::json(
            $results
            ->map(fn(AbstractModel $model) => $model->toArray())
            ->collect()
        );
    }






    public static function updateCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        if (!($primaryKey = $model::getPrimaryKey()))
            throw new Exception("Cannot update a model without a primary key");

        if (!($primaryKeyValue = $request->params($primaryKey)))
            return Response::json("A primary key [$primaryKey] is needed to update !", 401);

        $query = new ModelQuery($model, ModelQuery::UPDATE);
        $query->where($primaryKey, $primaryKeyValue);

        foreach($request->all() as $key => $value)
        {
            if ($key === $primaryKey)
                continue;
            $query->set($key, $value);
        }

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnUpdateBefore($model, $primaryKeyValue, $query));

        $query->fetch();

        $events->dispatch(new AutobahnUpdateAfter($model, $primaryKeyValue, $query));

        return Response::json("DONE", ResponseCodes::CREATED);
    }





    public static function deleteCallback(Request $request): Response
    {
        list($model, $middlewares) = self::extractRouteData($request);

        $query = new ModelQuery($model, ModelQuery::DELETE);

        if (!count($request->all()))
            return Response::json("At least one filter must be given", ResponseCodes::CONFLICT);

        foreach ($request->all() as $key => $value)
            $query->where($key, $value);

        foreach ($middlewares as $middleware)
            $middleware($query);

        $events = EventListener::getInstance();
        $events->dispatch(new AutobahnDeleteBefore($model, $query));

        $query->fetch();

        $events->dispatch(new AutobahnDeleteAfter($model, $query));

        return Response::json("DONE");
    }
}