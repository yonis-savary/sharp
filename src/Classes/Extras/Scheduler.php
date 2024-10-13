<?php

namespace YonisSavary\Sharp\Classes\Extras;

use DateTime;
use Exception;
use InvalidArgumentException;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\ObjectArray;
use YonisSavary\Sharp\Classes\Extras\SchedulerHandler;

class Scheduler
{
    use Component;

    protected array $handlers = [];

    public function schedule(string $cronExpression, callable $callback, string $identifier=null, bool $condition=true)
    {
        if (!$condition)
            return;

        if (!$identifier)
        {
            $match = [];
            preg_match(
                "/\w+\.php\(\d+\)/",
                (new Exception("thing"))->getTraceAsString(),
                $match
            );
            $identifier = $match[0] ?? "generic-identifier";
        }

        if (array_key_exists($identifier, $this->handlers))
            throw new InvalidArgumentException("Duplicate schedule identifier [$identifier]");

        if ($condition)
            $this->handlers[$identifier] = new SchedulerHandler($identifier, $cronExpression, $callback);
    }

    public function listAll()
    {
         return $this->handlers;
    }

    public function executeAll(DateTime $dateTime=null)
    {
        $dateTime ??= new DateTime();
        $dateTimeString = $dateTime->format("Y-m-d H:i:s");

        $logger = new Logger("schedule/scheduler.csv");
        $logger->info("Execution at $dateTimeString");

        ObjectArray::fromArray(array_values($this->handlers))
        ->forEach(fn(SchedulerHandler $handler) => $handler->launchIfValid($dateTime));
    }
}