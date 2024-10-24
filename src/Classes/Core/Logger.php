<?php

namespace YonisSavary\Sharp\Classes\Core;

use InvalidArgumentException;
use JsonException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Env\Storage;
use Throwable;
use YonisSavary\Sharp\Classes\Http\Request;

class Logger implements LoggerInterface
{
    use Component;

    protected $stream = null;
    protected bool $closeStream = true;
    protected string $filename;

    public static function getDefaultInstance()
    {
        return new self('sharp.csv');
    }

    /**
     * Create a logger from a stream (which must be writable)
     *
     * @param resource $stream Output stream to write to
     * @param bool $autoClose If `true`, the Logger will close the stream on destruct
     */
    public static function fromStream(mixed $stream, bool $autoClose=false): self
    {
        $logger = new self();
        $logger->replaceStream($stream, $autoClose);

        return $logger;
    }

    /**
     * @param ?string $filename File BASENAME for the Logger
     * @param ?Storage $storage Optional target Storage directory (global instance if `null`)
     * @example NULL `new Logger('error.csv', new Storage('/var/log/sharp/my-app'))`
     */
    public function __construct(string $filename=null, Storage $storage=null)
    {
        if (!$filename)
            return;

        $storage ??= Storage::getInstance()->getSubStorage('Logs');

        $exists = $storage->isFile($filename);
        if (!$exists)
            $storage->assertIsWritable();

        $this->filename = $storage->path($filename);
        $this->stream = $storage->getStream($filename, 'a', false);

        if (!$exists)
            fputcsv($this->stream, ['DateTime', 'IP', 'Method', 'Level', 'Message'], "\t");
    }

    public function __destruct()
    {
        $this->closeStream();
    }

    protected function closeStream(): void
    {
        if ($this->closeStream && $this->stream)
            fclose($this->stream);
    }

    /**
     * Replace the Logger stream with another
     *
     * @param resource $stream Output stream that replace the current one
     * @param bool $autoClose If `true`, the Logger will close the stream on destruct
     */
    public function replaceStream(mixed $stream, bool $autoClose=false): void
    {
        if (!is_resource($stream))
            throw new InvalidArgumentException('$stream parameter must be a resource');

        $this->closeStream();
        $this->stream = $stream;
        $this->closeStream = $autoClose;
    }

    /**
     * @return string Absolute path to the Logger's output file
     */
    public function getPath(): string
    {
        return $this->filename;
    }

    /**
     * @return string `$content` represented as a string
     */
    protected function toString(mixed $content): string
    {
        if ($content instanceof Throwable)
            return $this->getThrowableAsString($content);

        if (is_string($content) || is_numeric($content))
            return strval($content);

        try
        {
            return json_encode($content, JSON_THROW_ON_ERROR);
        }
        catch (JsonException)
        {
            return print_r($content, true);
        }
    }

    /**
     * Directly log a line(s) into the output stream
     */
    public function log($level, mixed $message, array $context=[]): void
    {
        if (!is_resource($this->stream))
            return;

        /** @var Request */
        $currentRequest = Context::get(Request::class, Request::fromGlobals());

        $ip = $currentRequest->getIp();
        $method = $currentRequest->getMethod();
        $now = date('Y-m-d H:i:s');

        $message = $this->toString($message);

        foreach ($context as $key => $value)
        {
            $value = $this->toString($value);
            $message = str_replace('{'.$key.'}', $value, $message);
        }

        foreach (explode("\n", trim($message)) as $line)
            fputcsv($this->stream, [$now, $ip, $method, $level, $line], "\t");
    }

    /**
     * Log a throwable message into the output plus its trace
     * (Useful to debug a trace and/or errors)
     */
    protected function getThrowableAsString(Throwable $throwable): string
    {
        return 'Got an ['. $throwable::class .'] Throwable: '. $throwable->getMessage() . "\n".
            sprintf('#- %s(%s)', $throwable->getFile(), $throwable->getLine()) . "\n" .
            $throwable->getTraceAsString();
    }

    /**
     * Log a 'debug' level line
     * @param mixed ...$messages Information/Objects to log (can be of any type)
     */
    public function debug(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function info(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function notice(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function warning(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function critical(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function alert(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function emergency(mixed $message, array $context=[]): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

}