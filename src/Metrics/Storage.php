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
 * Metrics:
 * 
 * <prefix>_http_duration_seconds_bucket     (le, <labels>)
 * <prefix>_http_memory_usage_bytes_bucket   (le, <labels>)
 * <prefix>_http_memory_usage_bytes          (route, <labels>)
 * <prefix>_http_requests_count              (route, status, <labels>)
 * <prefix>_http_runtime_seconds             (route, timer, <labels>)
 * <prefix>_<counter>_count                  (<labels>)
 */
class Storage
{
    private CollectorRegistry $registry;

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
            case "redis":
                $adapter = self::redisAdapter($redisConfig);
                break;
            case "apc":
                $adapter = new APC();
                break;
            case "in-memory":
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
            "host" => "127.0.0.1",
            "port" => 6379,
            "password" => null,
            "database" => 1,
            "timeout"  => 1,
            "read_timeout" => 1,
            "persistent_connections" => false,
        ];

        return new Redis($config + $defaults);
    }

    public function persist(Metrics $metrics): void
    {
        $runtime = $metrics->runtime()->fetchResultsInSeconds();

        $this->persistRequestsCounter($metrics);
        // $this->persistMethodRequestsCounter($metrics);
        $this->persistMemoryUsage($metrics);
        $this->persistEventCounters($metrics);
        $this->persistTimers($metrics);
        $this->persistRequestDuration($metrics);
    }

    private function persistRequestsCounter(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            "route"  => $metrics->httpRoute(), 
            "status" => $metrics->httpStatus()
        ]);

        $counter = $this->registry->getOrRegisterCounter(
            $metrics->namespace(),
            "http_requests_count",
            "Count HTTP requests processed",
            array_keys($labels)
        );

        $counter->inc($labels);
    }

    private function persistMemoryUsage(Metrics $metrics): void
    {
        $value  = $metrics->memoryUsage();
        $labels = $metrics->labels()->allWith([
            "route" => $metrics->httpRoute()
        ]);

        $gauge = $this->registry->getOrRegisterGauge(
            $metrics->namespace(),
            "http_memory_usage_bytes",
            "Memory usage of bytes",
            array_keys($labels)
        );

        $gauge->set($value, $labels);
        $labels = $metrics->labels()->all();

        $histogram = $this->registry->getOrRegisterHistogram(
            $metrics->namespace(),
            "http_memory_usage_bytes",
            "Histogram of HTTP memory usage in bytes",
            array_keys($labels),
            $metrics->memoryUsageBuckets()
        );

        $histogram->observe($value, $labels);
    }

    private function persistTimers(Metrics $metrics): void
    {
        $labels = $metrics->labels()->allWith([
            "route" => $metrics->httpRoute(),
            "timer" => null,
        ]);

        $labelKeys = array_keys($labels);

        $timers = $metrics->runtime()->fetchResultsInSeconds();
        foreach ($timers as $name => $value) {
            $gauge = $this->registry->getOrRegisterGauge(
                $metrics->namespace(),
                "http_runtime_seconds",
                "HTTP request rutime in seconds",
                $labelKeys
            );
            $labels["timer"] = $name;
            $gauge->set($value, $labels);
        }
    }

    private function persistRequestDuration(Metrics $metrics): void
    {
        $labels = $metrics->labels()->all();

        $histogram = $this->registry->getOrRegisterHistogram(
            $metrics->namespace(),
            "http_duration_seconds",
            "Histogram of HTTP request duration",
            array_keys($labels),
            $metrics->requestDurationBuckets()
        );

        $value = \array_sum(
            $metrics->runtime()->fetchResultsInSeconds()
        );

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
                        "{$name}_count",
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
