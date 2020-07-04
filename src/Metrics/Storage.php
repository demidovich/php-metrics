<?php

namespace Metrics;

use Metrics\Metrics;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Storage
{
    private $registry;
    private $metrics;
    private $logger;

    public function __construct(CollectorRegistry $registry, Metrics $metrics)
    {
        $this->registry = $registry;
        $this->metrics  = $metrics;

        register_shutdown_function([$this, 'persist']);
    }

    /**
     * @param string $adapter in-memory, apc, redis
     * @param array $redisConfig
     * @param Metrics $metrics
     * @return \self
     * @throws RuntimeException
     */
    public static function create(string $adapter, array $redisConfig, Metrics $metrics): self
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
                throw new RuntimeException("Invalid CollectorRegistry adapter \"$adapter\". Correct values: apc, in-memory, redis");
        }

        $registry = new CollectorRegistry($adapter);

        return new self($registry, $metrics);
    }

    private static function redisAdapter(array $config): Redis
    {
        $defaults = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 1,
            'timeout'  => 1,
            'read_timeout' => 1,
            'persistent_connections' => false,
        ];

        return new Redis($config + $defaults);
    }

    public function debug(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function persist(): void
    {
        $namespace = $this->metrics->namespace();
        $memory    = $this->metrics->memoryUsage();
        $labels    = $this->metrics->labels()->all();
        $counters  = $this->metrics->counters()->all();
        $timers    = $this->metrics->runtime()->allInSeconds();

        $this->persistRequests($namespace, $labels);
        $this->persistMemoryUsage($namespace, $labels, $memory);
        $this->persistCounters($namespace, $counters);
        $this->persistTimers($namespace, $labels, $timers);

        if ($this->logger) {
            $this->logger->info('metrics');
            $this->logger->info('namespace : ' . $namespace);
            $this->logger->info('memory    : ' . round($memory / (1024 * 1024), 2) . 'Mb');
            $this->logger->info('labels    : ' . print_r($labels, true));
            $this->logger->info('timers    : ' . print_r($timers, true));
            $this->logger->info('counters  : ' . print_r($counters, true));
        }
    }
    
    private function persistMemoryUsage(string $namespace, array $labels, int $value): void
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

    private function persistTimers(string $namespace, array $labels, array $timers): void
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
                    "{$name}_total", 
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