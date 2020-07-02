<?php

namespace Metric;

use Metric\MetricRepository;
use Metric\MetricLogger;
use Prometheus\CollectorRegistry;
use Psr\Log\LoggerInterface;

class Metric
{
    private $label;
    private $counters = [];
    private $timer;
    private $namespace;

    /**
     * @param int $startTime Start time in nanoseconds
     */
    public function __construct(int $startTime, string $namespace = 'app')
    {
        $this->timer = new MetricTimer($startTime);
        $this->label = new MetricLabel();
        $this->namespace = $namespace;

        $this->initLabels();
    }

    protected function initLabels(): void
    {
        $this->setMethodLabel('get');
        $this->setRouteLabel('none');
        $this->setStatusLabel(200);
    }

    public function setLabel(string $name, $value): void
    {
        $this->label->set($name, $value);
    }

    public function setMethodLabel(string $method): void
    {
        $this->label->set('method', $method);
    }

    public function setRouteLabel(string $route): void
    {
        $this->label->set('route', $route);
    }

    public function setStatusLabel(int $status): void
    {
        $this->label->set('status', $status);
    }

    public function labels(): array
    {
        return $this->label->all();
    }

    protected function start(string $timer): void
    {
        $this->timer->start($timer);
    }

    protected function spent(string $timer, int $nanoseconds): void
    {
        $this->timer->spent($timer, $nanoseconds);
    }

    public function startPhp(): void
    {
        $this->timer->start(MetricTimer::RUNTIME_PHP);
    }

    public function timersInSeconds(): array
    {
        return $this->timer->values(1e-9);
    }

    public function timersInMilliseconds(): array
    {
        return $this->timer->values(1e-6);
    }

    public function increaseCounter(string $counter, int $quantity = 1): void
    {
        if (! isset($this->counters[$counter])) {
            $this->counters[$counter] = 0;
        }

        $this->counters[$counter] += $quantity;
    }

    /**
     * Memory usage in bytes
     * @return int
     */
    public function memoryUsage(): int
    {
        return \memory_get_usage(false);
    }

    public function initStorage(CollectorRegistry $registry): void
    {
        $repository = new MetricRepository(
            $registry, 
            $this, 
            $this->namespace
        );

        register_shutdown_function([$repository, 'persist']);
    }

    public function initLogger(LoggerInterface $logger): void
    {
        register_shutdown_function(function() use ($logger) {
            $logger->info('metric labels : '.print_r($this->labels, true));
            $logger->info('metric timers : '.print_r($this->timers, true));
            $logger->info('metric memory : '.print_r($this->memory, true));
            $logger->info('metric counters : '.print_r($this->counters, true));
        });
    }
}