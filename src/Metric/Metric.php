<?php

namespace Metric;

class Metric
{
    protected $namespace = 'app';

    private $label;
    private $counters = [];
    private $timer;

    /**
     * @param int $startTime Start time in nanoseconds
     */
    public function __construct(int $startTime)
    {
        $this->timer = new MetricTimer($startTime);
        $this->label = new MetricLabel();

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
        return $this->timer->values(1e9, 6);
    }

    public function timersInMilliseconds(): array
    {
        return $this->timer->values(1e6, 2);
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