<?php

namespace YonisSavary\Sharp\Classes\Http;

use YonisSavary\Sharp\Classes\Http\Configuration\EventSourceConfiguration;

class EventSource
{
    const MESSAGE_END = "\n\n";
    const LINE_END = "\n";

    protected $started = false;

    protected EventSourceConfiguration $configuration;

    public function __construct(
        EventSourceConfiguration $configuration=null
    )
    {
        $this->configuration = $configuration ?? EventSourceConfiguration::resolve();
    }

    public function start(string $startEvent=null, bool $sendHeaders=true)
    {
        if ($this->started)
            return;
        $this->started = true;

        if ($sendHeaders)
        {
            header('Cache-Control: no-store');
            header('Content-Type: text/event-stream');
        }

        $startEvent ??= $this->configuration->startEvent;

        if ($startEvent)
            $this->send($startEvent);
    }

    protected function sendMessage(string $message)
    {
        if (!$this->started)
            $this->start();

        echo $message . self::MESSAGE_END;
        flush();

        if (connection_aborted())
            die;
    }

    public function send(string $event, mixed $data=null, $id=null, int $retry=null)
    {
        $id = $id ? "id: $id": null;
        $retry = $retry ? "retry: $retry": null;

        $message = join(self::LINE_END, array_filter([
            "event: $event" ,
            'data: '. json_encode($data),
            $id,
            $retry,
        ]));

        $this->sendMessage($message);
    }

    public function data(mixed $data)
    {
        $this->sendMessage('data: '. json_encode($data));
    }

    public function end(string $endEvent=null)
    {
        $endEvent ??= $this->configuration->endEvent;

        if ($endEvent)
            $this->send($endEvent);

        $this->started = false;

        if ($this->configuration->dieOnEnd)
            die;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }
}