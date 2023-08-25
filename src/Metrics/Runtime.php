<?php

namespace Metrics;

class Runtime
{
    public const PHP_INIT = "php_init";
    public const PHP = "php";

    private Timer $timer;

    private array $timers = [];

    /**
     * @param int $startTime Application start time in nanoseconds
     */
    public function __construct(int $startNanosecondsTime)
    {
        $this->timer = new Timer(self::PHP_INIT, $startNanosecondsTime);
    }

    /**
     * Start the next timer
     * @param string $timer
     * @return void
     */
    public function start(string $timer): void
    {
        if ($timer === $this->timer->name) {
            return;
        }

        $this->timer->stop();
        $this->timers[] = $this->timer;
        $this->timer = Timer::new($timer);
    }

    public function spent(Timer $timer): void
    {
        $this->timer->subTime($timer);

        $this->timers[] = $timer;
    }

    public function fetchResultsInSeconds(int $precision = 6): array
    {
        $this->timer->stop();
        $this->timers[] = $this->timer;

        $results = [];
        /** @var Timer $timer */
        foreach ($this->timers as $timer) {
            if (! isset($results[$timer->name])) {
                $results[$timer->name] = $timer->toSeconds($precision);
            } else {
                $results[$timer->name] += $timer->toSeconds($precision);
            }
        }

        return $results;
    }
}
