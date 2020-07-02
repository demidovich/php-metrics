<?php

namespace Metric;

use Metric\Metric;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class MetricRepository
{
    private $registry;
    private $namespace;
    private $metric;

    public function __construct(CollectorRegistry $registry, Metric $metric, string $namespace = 'app')
    {
        $this->registry  = $registry;
        $this->namespace = $namespace;
        $this->metric    = $metric;
    }

    public function persist(): void
    {
        $labels   = $this->metric->labels();
        $timers   = $this->metric->timersInSeconds();
        $memory   = $this->metric->memoryUsage();
        $counters = $this->metric->counters();

        $this->persistMemoryUsage($memory, $labels);
        $this->persistTimers($timers, $labels);
        $this->persistRequests($labels);
        $this->persistCounters($counters);
    }

    private function persistMemoryUsage(int $value, array $labels): void
    {
        $memoryUsage = $this->registry->getOrRegisterGauge(
            $this->namespace, 
            "http_memory_usage_total", 
            "Memory usage of bytes", 
            array_keys($labels)
        );

        $memoryUsage->set($value, $labels);
    }

    private function persistRequests(array $labels): void
    {
        $requests = $this->registry->getOrRegisterCounter(
            $this->namespace, 
            'http_requests_total', 
            'Total HTTP requests processed by the Yazoo', 
            array_keys($labels)
        );

        $requests->inc($labels);
    }

    private function persistTimers(array $timers, array $labels): void
    {
        if (! $timers) {
            return;
        }

        $labelNames = array_keys($labels);

        foreach ($timers as $name => $value) {
            if ($value) {
                $timer = $this->registry->getOrRegisterGauge(
                    $this->namespace, 
                    "http_runtime_{$name}", 
                    "{$name} runtime in microseconds", 
                    $labelNames
                );
                $timer->set($value, $labels);
            }
        }
    }

    private function persistCounters(array $counters): void
    {
        if (! $counters) {
            return;
        }

        foreach ($counters as $name => $value) {
            if ($value) {
                $counter = $this->registry->getOrRegisterCounter(
                    $this->namespace, 
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