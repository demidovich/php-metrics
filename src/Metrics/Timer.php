<?php

namespace Metrics;

class Timer
{
    private int $nanosecondsTotal = 0;

    public function __construct(
        public readonly string $name, 
        private int $nanosecondsStart = 0,
    ) {
    }

    public static function new(string $name): self
    {
        return new self(
            $name,
            hrtime(true)
        );
    }

    public static function stoppedFromNanoseconds(string $name, int $value): self
    {
        return self::fromValue($name, $value, 1);
    }

    public static function stoppedFromMicroseconds(string $name, int|float $value): self
    {
        return self::fromValue($name, $value, 1e3);
    }

    public static function stoppedFromMilliseconds(string $name, int|float $value): self
    {
        return self::fromValue($name, $value, 1e6);
    }

    public static function stoppedFromSeconds(string $name, int|float $value): self
    {
        return self::fromValue($name, $value, 1e9);
    }

    private static function fromValue(string $name, int $value, int $multiplier): self
    {
        $self = new self($name, 0);
        $self->nanosecondsTotal = $value * $multiplier;

        return $self;
    }

    public function stop(): void
    {
        $this->nanosecondsTotal = hrtime(true) - $this->nanosecondsStart;
    }

    public function addTime(Timer $timer): void
    {
        $this->nanosecondsStart = $this->nanosecondsStart - $timer->toNanoseconds();
    }

    public function subTime(Timer $timer): void
    {
        $this->nanosecondsStart = $this->nanosecondsStart + $timer->toNanoseconds();
    }

    public function toNanoseconds(): int
    {
        return $this->nanosecondsTotal;
    }

    // public function toMicroseconds(): int
    // {
    //     return round($this->nanosecondsTotal / 1e3);
    // }

    // public function toMilliseconds(): int
    // {
    //     return round($this->nanosecondsTotal / 1e6);
    // }

    public function toSeconds(int $precision = 6): float
    {
        return round($this->nanosecondsTotal / 1e9, $precision);
    }
}
