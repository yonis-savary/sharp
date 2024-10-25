<?php

namespace YonisSavary\Sharp\Classes\Http;

use YonisSavary\Sharp\Classes\Core\Configurable;

class EventSource
{
    use Configurable;

    const MESSAGE_END = "\n\n";
    const LINE_END = "\n";

    protected $started = false;

    public static function getDefaultConfiguration(): array
    {
        return [
            'use-default-event-name' => true,
            'start-event' => 'event-source-start',
            'end-event' => 'event-source-end',
            'die-on-end' => true
        ];
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

        if ($this->configuration['use-default-event-name'])
            $startEvent ??= $this->configuration['start-event'];

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
        if ($this->configuration['use-default-event-name'])
            $endEvent ??= $this->configuration['end-event'];

        if ($endEvent)
            $this->send($endEvent);

        $this->started = false;

        if ($this->configuration['die-on-end'])
            die;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }
}