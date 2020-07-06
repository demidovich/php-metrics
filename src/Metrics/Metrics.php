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
    protected $namespace = 'app';

    private $labels;
    private $runtime;
    private $counters;
    private $storage;

    /**
     * @param int $startTime Application start time in nanoseconds
     */
    public function __construct(int $startTime, array $labels = [])
    {
        $this->runtime  = new Runtime($startTime);
        $this->labels   = new Labels($labels);
        $this->counters = new Counters();
    }

    public function initStorage(Storage $storage): void
    {
        $this->storage = $storage;

        register_shutdown_function(function() {
            $this->beforePersist();
            $this->storage->persist($this);
        });
    }

    public function initDebug(LoggerInterface $logger): void
    {
        $debug = new Debug($logger);

        register_shutdown_function(function() use ($debug) {
            $debug->toLog($this);
        });
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

    /**
     * Called before persisting the metrics
     * 
     * @return void
     */
    public function beforePersist(): void
    {
    }
}
