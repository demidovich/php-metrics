<?php

namespace Metrics;

use Metrics\Counters;
use Metrics\Labels;
use Metrics\Runtime;
use Metrics\Storage;
use Metrics\Debug;
use Psr\Log\LoggerInterface;

class Metrics
{
    protected $namespace = "app";

    private $httpRoute  = "undefined";
    private $httpMethod = "undefined";
    private $httpStatus = 0;
    private $labels;
    private $runtime;
    private $counters;
    private $storage;

    // 512k 1M 1.5M 2M 2.5M 3M 3.5M 4M 4.5M 5M 10M 15M
    protected $memoryUsageBuckets = [
        524288,  1048576, 1572864, 2097152, 2621440, 3145728, 
        3670016, 4194304, 4718592, 5242880, 10485760, 15728640
    ];

    protected $requestDurationBuckets = [
        0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5
    ];

    /**
     * @param int $startTime Application start time in nanoseconds
     * @param array $labels Additional global labels (node, server etc)
     */
    public function __construct(
        int $startTime, 
        array $labels = []
    ) {
        $this->runtime  = new Runtime($startTime);
        $this->labels   = new Labels($labels);
        $this->counters = new Counters();
    }

    public function initStorage(Storage $storage): void
    {
        $this->storage = $storage;

        register_shutdown_function([$storage, "persist"], $this);
    }

    public function initDebug(LoggerInterface $logger): void
    {
        $debug = new Debug($logger);

        register_shutdown_function([$debug, "toLog"], $this);
    }

    public function setHttpRoute(string $value): void
    {
        $this->httpRoute = $value;
    }

    public function setHttpMethod(string $value): void
    {
        $this->httpMethod = $value;
    }

    public function setHttpStatus(int $value): void
    {
        $this->httpStatus = $value;
    }

    public function httpRoute(): string
    {
        return $this->httpRoute;
    }

    public function httpMethod(): string
    {
        return $this->httpMethod;
    }

    public function httpStatus(): string
    {
        return $this->httpStatus;
    }

    public function storage(): Storage
    {
        return $this->storage;
    }

    public function startPhp(): void
    {
        $this->runtime->start(Runtime::PHP);
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function counters(): Counters
    {
        return $this->counters;
    }

    public function labels(): Labels
    {
        return $this->labels;
    }

    public function runtime(): Runtime
    {
        return $this->runtime;
    }

    public function memoryUsage(): int
    {
        return \memory_get_usage(false);
    }

    public function memoryUsageBuckets(): array
    {
        return $this->memoryUsageBuckets;
    }

    public function requestDurationBuckets(): array
    {
        return $this->requestDurationBuckets;
    }
}
