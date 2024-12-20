<?php

namespace YonisSavary\Sharp\Classes\Test;

use Symfony\Component\Process\Process;
use YonisSavary\Sharp\Classes\Core\Component;
use YonisSavary\Sharp\Classes\Core\Logger;
use YonisSavary\Sharp\Core\Utils;

/**
 * ShapServer is an object respresenting a PHP Built-in server process
 */
class SharpServer
{
    use Component;

    protected Process $process;

    protected string $hostname;
    protected string $protocol;
    protected ?int $port;

    /**
     * Create a Sharp self-server and launch it
     *
     * @param int $port PHP Server port (If none provider, a random port is chose between 0 and 65534)
     * @param string $hostname PHP Server hostname, `localhost` by default
     * @param int $safeDelay Delay to wait after launching the server as PHP may take a little time initializing it
     * @param string $publicDirectory Directory which contains `index.php`, if none is provided, a default one is chosen
     */
    public function __construct(
        int $port=null,
        string $publicDirectory=null,
        string $hostname='localhost',
        string $protocol="http://"
    )
    {
        $logger = Logger::getInstance();

        if (!str_ends_with($protocol, "://"))
            $protocol = "$protocol://";
        $this->protocol = $protocol;

        $this->hostname = $hostname;
        $this->port = $port ?? random_int(8000, 65534);

        $publicDirectory ??= Utils::relativePath('Public');
        if (!is_dir($publicDirectory))
        {
            $logger->warning('{directory} does not exists', ['directory' => $publicDirectory]);
            return;
        }

        $url = $this->hostname . ':' . $this->getPort();

        $logger->info(
            'Starting self-server on port {port} in directory {directory}',
            ['port' => $this->port, 'directory' => $publicDirectory]
        );

        $this->process = new Process(['php','-S',$url, "index.php"], $publicDirectory);
        $this->process->start();

        usleep(50*1000);
    }

    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Stop the self-server instance if running
     */
    public function stop(): void
    {
        if ($this->process && (!$this->process->isRunning()))
            return;

        Logger::getInstance()->info('Stopping self-server on port {port}', ['port' => $this->port]);
        $this->process->stop();
    }

    /**
     * Get the server current port
     */
    public function getPort(): int
    {
        return $this->port;
    }

    public function isRunning(): bool
    {
        if (!$this->process)
            return false;

        return $this->process->isRunning();
    }

    /**
     * Get an URL to connect to the self-server
     */
    public function getURL(string $path=null): string
    {
        $origin = $this->protocol . $this->hostname . ':' . $this->getPort();

        $url = $origin;

        if ($path && (!str_starts_with($path, '/')))
            $path = "/$path";

        if ($path)
            $url .= $path;

        return $url;
    }


    public function getOutput(): ?string
    {
        return $this->process ?
            $this->process->getOutput():
            null;
    }


    public function getErrorOutput(): ?string
    {
        return $this->process ?
            $this->process->getErrorOutput():
            null;
    }

    public function getIncrementalOutput(): ?string
    {
        return $this->process ?
            $this->process->getIncrementalOutput():
            null;
    }

    public function getIncrementalErrorOutput(): ?string
    {
        return $this->process ?
            $this->process->getIncrementalErrorOutput():
            null;
    }
}