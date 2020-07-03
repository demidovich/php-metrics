<?php

namespace Metrics;

class Metrics
{
    protected $namespace = 'app';

    private $labels;
    private $runtime;
    private $counters = [];

    /**
     * @param int $startTime Application start time in nanoseconds
     */
    public function __construct(int $startTime)
    {
        $this->labels  = new Labels();
        $this->runtime = new Runtime($startTime);

        $this->initLabels();
    }

    protected function initLabels(): void
    {
        $this->setMethodLabel('get');
        $this->setRouteLabel('none');
        $this->setStatusLabel(200);
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    protected function setLabel(string $name, $value): void
    {
        $this->labels->set($name, $value);
    }

    public function setMethodLabel(string $method): void
    {
        $this->labels->set('method', $method);
    }

    public function setRouteLabel(string $route): void
    {
        $this->labels->set('route', $route);
    }

    public function setStatusLabel(int $status): void
    {
        $this->labels->set('status', $status);
    }

    public function labels(): array
    {
        return $this->labels->all();
    }

    protected function start(string $timer): void
    {
        $this->runtime->start($timer);
    }

    protected function spent(string $timer, int $nanoseconds): void
    {
        $this->runtime->spent($timer, $nanoseconds);
    }

    public function startPhp(): void
    {
        $this->runtime->start(Runtime::PHP);
    }

    public function timersInSeconds(int $precision = 5): array
    {
        return $this->runtime->timers(1e9, $precision);
    }

    public function timersInMilliseconds(int $precision = 2): array
    {
        return $this->runtime->timers(1e6, $precision);
    }

    public function counters(): array
    {
        return $this->counters;
    }

    public function incrCounter(string $counter, int $quantity = 1): void
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
}