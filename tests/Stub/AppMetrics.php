<?php

namespace Tests\Stub;

use Metrics\Metrics;

class AppMetrics extends Metrics
{
    protected $namespace = "myapp";

    public function startMongo(): void
    {
        $this->runtime()->start('mongo');
    }

    public function spentMongo(int $microseconds): void
    {
        $this->runtime()->spent('mongo', $microseconds * 1000);
    }

    public function registerSigninAttempt(int $quantity = 1): void
    {
        $this->counters()->increase('signin_attempt', $quantity);
    }

    public function registerSigninSuccess(): void
    {
        $this->counters()->increase('signin_success');
    }
}