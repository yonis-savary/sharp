<?php

namespace YonisSavary\Sharp\Classes\Extras;

use DateTime;
use Psr\Log\LoggerInterface;
use Throwable;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Classes\Data\ObjectArray;

class SchedulerHandler
{
    public $callback;
    protected ?LoggerInterface $logger = null;

    public function __construct(
        public string $identifier,
        public string $cronExpression,
        callable $callback,
    ){
        $this->callback = $callback;
    }

    public function getLogger(): LoggerInterface
    {
        if (!$this->logger)
        {
            $fileSafeidentifier = preg_replace("/[^\w]/", "_", $this->identifier);
            $this->logger = new Logger("schedule/" . $fileSafeidentifier . ".csv");
        }
        return $this->logger;
    }

    public function isValid(DateTime $dateTime): bool
    {
        $cronExpression = new CronExpression($this->cronExpression);
        return $cronExpression->matches($dateTime);
    }

    public function toSentence(): string
    {
        try
        {
            $cronExpression = new CronExpression($this->cronExpression);
            return $cronExpression->toSentence();
        }
        catch (Throwable $err)
        {
            $this->logger->warning("Could not transform cron expression [{expression}] into a sentence", ["expression" => $this->cronExpression]);
            $this->logger->warning($err);
        }
    }

    public function launchIfValid(DateTime $dateTime)
    {
        if ($this->isValid($dateTime))
            $this->launch($dateTime);
    }

    public function launch(DateTime $dateTime=null)
    {
        $dateTime ??= new DateTime();

        $logger = $this->getLogger();
        $logger->info("Launching task at " . $dateTime->format("Y-m-d H:i:s"));

        ob_start();

        try
        {
            ($this->callback)();
        }
        catch (Throwable $err)
        {
            $logger->error("Error while launching task");
            $logger->error($err);
        }

        if ($output = ob_get_clean())
        {
            ObjectArray::fromExplode("\n", $output)
            ->filter(fn($x) => $x !== "")
            ->forEach(fn($x) => $logger->info($x));
        }

    }
}