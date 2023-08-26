<?php

namespace Tests\Stub;

use Metrics\Metrics;
use Metrics\Timer;

class AppMetrics extends Metrics
{
    protected string $namespace = "myapp";

    public function startMongo(): void
    {
        $this->runtime()->start("mongo");
    }

    public function spentMongo(int $microseconds): void
    {
        $timer = Timer::stoppedFromMicroseconds("mongo", $microseconds);

        $this->runtime()->spent($timer);
    }

    public function registerSigninAttempt(int $quantity = 1): void
    {
        $this->counters()->increase("signin_attempt", $quantity);
    }

    public function registerSigninSuccess(): void
    {
        $this->counters()->increase("signin_success");
    }
}