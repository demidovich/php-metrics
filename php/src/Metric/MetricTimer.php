<?php

namespace Metric;

class MetricTimer
{
    public const RUNTIME_PHP_INIT = 'php_init';
    public const RUNTIME_PHP = 'php';

    private $timer;
    private $timerStartedAt;

    private $timers = [];

    public function __construct(int $startTime)
    {
        $this->timer = self::RUNTIME_PHP_INIT;
        $this->timerStartedAt = $startTime;
        $this->timers[self::RUNTIME_PHP_INIT] = 0;
    }

    /**
     * Start the next timer
     * @param string $timer
     * @return void
     */
    public function start(string $timer): void
    {
        if ($timer === $this->timer) {
            return;
        }

        if (! isset($this->timers[$timer])) {
            $this->timers[$timer] = 0;
        }

        $finishedAt = $this->stop();

        $this->timer = $timer;
        $this->timerStartedAt = $finishedAt;
    }

    public function spent(string $timer, int $nanoseconds): void
    {
        if ($this->phpInitComplete()) {
            $this->timers[self::RUNTIME_PHP] = $this->timers[self::RUNTIME_PHP] - $nanoseconds;
        } else {
            $this->timers[self::RUNTIME_PHP_INIT] = $this->timers[self::RUNTIME_PHP_INIT] - $nanoseconds;
        }

        if (! isset($this->timers[$timer])) {
            $this->timers[$timer] = 0;
        }

        $this->timers[$timer] += $nanoseconds;
    }

    private function phpInitComplete(): bool
    {
        return $this->timer !== self::RUNTIME_PHP_INIT;
    }

    /**
     * Stop active timer
     * @return int
     */
    private function stop(): int
    {
        $time = hrtime(true);
        $this->timers[$this->timer] += ($time - $this->timerStartedAt);

        return $time;
    }

    public function values(int $multiplier): array
    {
        $this->stop();

        $result = [];
        foreach ($this->timers as $name => $value) {
            $result[$name] = \round($value * $multiplier, 5);
        }
        $result['total'] = \array_sum($result);

        return $result;
    }
}