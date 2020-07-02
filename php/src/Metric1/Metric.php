<?php

namespace Metric1;

class Metric
{
    private $httpMethod = 'get';
    private $httpRoute  = 'none';
    private $httpStatus = 200;

    // Таймеры runtime
    // При инициализации приложения стартует таймер PHP
    // Всегда активен какой либо таймер
    // Сценарий запуска следующего таймера останавливает предыдущий
    // При выгрузке таймеров timers() происходит автоматическое 
    // сохранение активного таймера

    private $timer;
    private $timerStartedAt;

    private $timers = [];

    private const RUNTIME_PHP_INIT = 'php_init';
    private const RUNTIME_PHP      = 'php';

    private $counters = [];

    /**
     * @param int $startTime Start time in nanoseconds
     */
    public function __construct(int $startTime)
    {
        $this->timer = self::RUNTIME_PHP_INIT;
        $this->timerStartedAt = $startTime;
        $this->timers[self::RUNTIME_PHP_INIT] = 0;
    }

    public function setHttpRoute(string $route): void
    {
        $this->httpRoute = $route;
    }

    public function httpRoute(): string
    {
        return $this->httpRoute;
    }

    public function setHttpMethod(string $method): void
    {
        $this->httpMethod = $method;
    }

    public function httpMethod(): string
    {
        return $this->httpMetod;
    }

    public function setHttpStatus(int $code): void
    {
        $this->httpStatus = $code;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
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

    public function startPhp(): void
    {
        $this->start(self::RUNTIME_PHP);
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

    public function increaseCounter(string $counter, int $quantity = 1): void
    {
        if (! isset($this->counters[$counter])) {
            $this->counters[$counter] = 0;
        }

        $this->counters[$counter] += $quantity;
    }

    private function timers(int $multiplier): array
    {
        $this->stop();

        $result = [];
        foreach ($this->timers as $name => $value) {
            $result[$name] = \round($value * $multiplier, 5);
        }
        $result['total'] = \array_sum($result);
        return $result;
    }

    public function timersInSeconds(): array
    {
        return $this->timers(1e-9);
    }

    public function timersInMilliseconds(): array
    {
        return $this->timers(1e-6);
    }

    public function counters(): array
    {
        return $this->counters;
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

        \register_shutdown_function([$repository, 'persist']);
    }

    public function initLogger(LoggerInterface $psrLogger): void
    {
        $logger = new MetricLogger($psrLogger, $this);

        \register_shutdown_function([$logger, 'log']);
    }
}