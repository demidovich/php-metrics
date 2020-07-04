<?php

namespace Metrics;

use Metrics\Counters;
use Metrics\Labels;
use Metrics\Runtime;

class Metrics
{
    protected $namespace = 'app';

    private $labels;
    private $runtime;
    private $counters;

    /**
     * @param int $startTime Application start time in nanoseconds
     */
    public function __construct(int $startTime, array $labels = [])
    {
        $this->runtime  = new Runtime($startTime);
        $this->labels   = new Labels($labels);
        $this->counters = new Counters();
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
}
