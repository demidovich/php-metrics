<?php

namespace Metrics;

class Runtime
{
    public const PHP_INIT = 'php_init';
    public const PHP = 'php';

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
     * Active timer
     */
    public function timer(): string
    {
        return $this->timer;
    }

    /**
     * Active timer start time
     */
    public function timerStartedAt()
    {
        return $this->timerStartedAt;
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

        $this->stop();
        $this->timer = $timer;
    }

    /**
     * Stop active timer
     * @return int
     */
    public function stop(): void
    {
        $finishedAt = hrtime(true);

        $this->timers[$this->timer] += ($finishedAt - $this->timerStartedAt);

        $this->timerStartedAt = $finishedAt;
    }

    public function spent(string $timer, int $nanoseconds): void
    {
        $runningTimer = $this->timer;

        $this->timers[$runningTimer] = $this->timers[$runningTimer] - $nanoseconds;

        if (! isset($this->timers[$timer])) {
            $this->timers[$timer] = 0;
        }

        $this->timers[$timer] += $nanoseconds;
    }

    public function allInSeconds(int $precision = 6): array
    {
        return $this->timers(1e9, $precision);
    }

    public function allInMilliseconds(int $precision = 2): array
    {
        return $this->timers(1e6, $precision);
    }

    private function timers(int $divider, int $precision): array
    {
        $result = [];
        foreach ($this->timers as $name => $value) {
            $result[$name] = \round($value / $divider, $precision);
        }

        return $result;
    }
}
