<?php

namespace Metrics;

use Metrics\Metrics;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use RuntimeException;

/**
 * Prometheus metrics:
 * 
 * app_http_requests_total          (route, method, labels)     count
 * app_http_statuses_total          (route, status, labels)     count
 * app_http_memory_usage_bytes      (route, labels)             bytes
 * app_http_runtime_seconds_total   (route, labels)             seconds
 * app_http_runtime_seconds         (route, timer, labels)      seconds
 * app_signin_attempt_total         (lebels)                    count
 */
class Storage
{
    private $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $adapter in-memory, apc, redis
     * @param array $redisConfig
     * @param Metrics $metrics
     * @return \self
     * @throws RuntimeException
     */
    public static function create(string $adapter, array $redisConfig = []): self
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
            default:
                throw new RuntimeException(
                    "Invalid CollectorRegistry adapter \"$adapter\". Correct values: apc, in-memory, redis"
                );
        }

        $registry = new CollectorRegistry($adapter);

        return new self($registry);
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

    public function persist(Metrics $metrics): void
    {
        $this->persistRequestsCounter($metrics);
        $this->persistStatusesCounter($metrics);
        $this->persistMemoryUsage($metrics);
        $this->persistEventCounters($metrics);
        $this->persistTimers($metrics);
        $this->persistTimersTotal($metrics);
        $this->persistRequestDuration($metrics);
    }

    private function persistRequestsCounter(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'route'  => $metrics->httpRoute(), 
            'method' => $metrics->httpMethod()
        ]);

        $counter = $this->registry->getOrRegisterCounter(
            $metrics->namespace(),
            'http_requests_total',
            'Total HTTP requests processed',
            array_keys($labels)
        );

        $counter->inc($labels);
    }

    private function persistStatusesCounter(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'route'  => $metrics->httpRoute(),
            'status' => $metrics->httpStatus()
        ]);

        $counter = $this->registry->getOrRegisterCounter(
            $metrics->namespace(),
            'http_statuses_total',
            'Total HTTP response statuses',
            array_keys($labels)
        );

        $counter->inc($labels);
    }
    
    private function persistMemoryUsage(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'route' => $metrics->httpRoute()
        ]);

        $gauge = $this->registry->getOrRegisterGauge(
            $metrics->namespace(),
            "http_memory_usage_bytes",
            "Memory usage of bytes",
            array_keys($labels)
        );

        $gauge->set($metrics->memoryUsage(), $labels);
    }

    private function persistTimers(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'route' => $metrics->httpRoute(),
            'timer' => null,
        ]);

        $labelKeys = array_keys($labels);

        foreach ($metrics->runtime()->allInSeconds() as $name => $value) {
            $gauge = $this->registry->getOrRegisterGauge(
                $metrics->namespace(),
                "http_runtime_seconds",
                "HTTP request rutime in seconds",
                $labelKeys
            );
            $labels['timer'] = $name;
            $gauge->set($value, $labels);
        }
    }

    private function persistTimersTotal(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'route' => $metrics->httpRoute()
        ]);

        $gauge = $this->registry->getOrRegisterGauge(
            $metrics->namespace(),
            "http_runtime_seconds_total",
            "HTTP request rutime total in seconds",
            array_keys($labels)
        );

        $value = \array_sum($metrics->runtime()->allInSeconds());
        
        $gauge->set($value, $labels);
    }

    private function persistRequestDuration(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            'method' => $metrics->httpMethod(),
            'status' => $metrics->httpStatus()
        ]);

        $histogram = $this->registry->getOrRegisterHistogram(
            $metrics->namespace(),
            "http_duration_seconds",
            "Histogram of HTTP request duration",
            array_keys($labels),
            [0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5]
        );

        $value = \array_sum($metrics->runtime()->allInSeconds());

        $histogram->observe($value, $labels);
    }

    private function persistEventCounters(Metrics $metrics): void
    {
        if (($counters = $metrics->counters()->all())) {

            $labels = $metrics->labels()->all();
            $labelKeys = array_keys($labels);

            foreach ($counters as $name => $value) {
                if ($value) {
                    $counter = $this->registry->getOrRegisterCounter(
                        $metrics->namespace(),
                        "{$name}_total",
                        "Count of {$name} event",
                        $labelKeys
                    );
                    $counter->incBy($value, $labels);
                }
            }
        }
    }

    public function fetch(): string
    {
        $samples = $this->registry->getMetricFamilySamples();

        return (new RenderTextFormat())->render($samples);
    }
}
