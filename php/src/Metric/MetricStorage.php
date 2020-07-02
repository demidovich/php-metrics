<?php

namespace Metric;

use Metric\Metric;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use RuntimeException;

class MetricStorage
{
    private $registry;
    private $metric;

    public function __construct(CollectorRegistry $registry, Metric $metric)
    {
        $this->registry = $registry;
        $this->metric   = $metric;
    }

    public static function fromConfig(string $adapter, array $redisConfig, Metric $metric): self
    {
        switch ($adapter) {
            case 'redis':
                $adapter = self::redisAdapter($redisConfig);
                break;
            case 'apc':
                $adapter = new APC();
                break;
            case 'in-memory':
                $adapter = new InMemory();
                break;
            default :
                throw new RuntimeException("Invalid CollectorRegistry adapter name. Correct values: apc, in-memody, redis");
        }

        $registry = new CollectorRegistry($adapter);

        return new self($registry, $metric);
    }

    private static function redisAdapter(array $config): Redis
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => (int) 1,
            'persistent_connections' => false,
            'timeout'  => 0.5,
            'read_timeout' => 2,
        ] + $config;

        return new Redis($config);
    }


    public function persist(): void
    {
        $namespace = $this->metric->namespace();
        $labels    = $this->metric->labels();
        $timers    = $this->metric->timersInSeconds();
        $memory    = $this->metric->memoryUsage();
        $counters  = $this->metric->counters();

        $this->persistMemoryUsage($namespace, $memory, $labels);
        $this->persistTimers($namespace, $timers, $labels);
        $this->persistRequests($namespace, $labels);
        $this->persistCounters($namespace, $counters);
    }

    private function persistMemoryUsage(string $namespace, int $value, array $labels): void
    {
        $memoryUsage = $this->registry->getOrRegisterGauge(
            $namespace, 
            "http_memory_usage_total", 
            "Memory usage of bytes", 
            array_keys($labels)
        );

        $memoryUsage->set($value, $labels);
    }

    private function persistRequests(string $namespace, array $labels): void
    {
        $requests = $this->registry->getOrRegisterCounter(
            $namespace, 
            'http_requests_total', 
            'Total HTTP requests processed by the Yazoo', 
            array_keys($labels)
        );

        $requests->inc($labels);
    }

    private function persistTimers(string $namespace, array $timers, array $labels): void
    {
        if (! $timers) {
            return;
        }

        $labelNames = array_keys($labels);

        foreach ($timers as $name => $value) {
            if ($value) {
                $timer = $this->registry->getOrRegisterGauge(
                    $namespace, 
                    "http_runtime_{$name}", 
                    "{$name} runtime in microseconds", 
                    $labelNames
                );
                $timer->set($value, $labels);
            }
        }
    }

    private function persistCounters(string $namespace, array $counters): void
    {
        if (! $counters) {
            return;
        }

        foreach ($counters as $name => $value) {
            if ($value) {
                $counter = $this->registry->getOrRegisterCounter(
                    $namespace, 
                    "quantity_{$name}_total", 
                    "Count of {$name}"
                );
                $counter->incBy($value);
            }
        }
    }
    
    public function fetch(): string
    {
        $samples = $this->registry->getMetricFamilySamples();

        return (new RenderTextFormat())->render($samples);
    }
}