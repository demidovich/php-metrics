<?php

namespace Metrics;

class Runtime
{
    const PHP_INIT = 'php_init';
    const PHP = 'php';

    private $timer;
    private $timerStartedAt;

    private $timers = [];

    /**
     * @param int $startTime Application start time in nanoseconds
     */
    public function __construct(int $startTime)
    {
        $this->timer = self::PHP_INIT;
        $this->timerStartedAt = $startTime;

        $this->timers[self::PHP_INIT] = 0;
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

    public function spent(string $timer, int $nanoseconds): void
    {
        if ($this->phpInitComplete()) {
            $this->timers[self::PHP] = $this->timers[self::PHP] - $nanoseconds;
        } else {
            $this->timers[self::PHP_INIT] = $this->timers[self::PHP_INIT] - $nanoseconds;
        }

        if (! isset($this->timers[$timer])) {
            $this->timers[$timer] = 0;
        }

        $this->timers[$timer] += $nanoseconds;
    }

    private function phpInitComplete(): bool
    {
        return $this->timer !== self::PHP_INIT;
    }

    public function timers(int $divider, int $precesion): array
    {
        $this->stop();

        $result = [];
        foreach ($this->timers as $name => $value) {
            $result[$name] = \round($value / $divider, $precesion);
        }
        
        $result['total'] = \array_sum($result);

        return $result;
    }
}