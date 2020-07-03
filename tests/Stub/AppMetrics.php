<?php

namespace Tests\Stub;

use Metrics\Metrics;

class AppMetrics extends Metrics
{
    protected $namespace = "myapp";

    public function startMongo(): void
    {
        $this->start('mongo');
    }

    public function spentMongo(int $microseconds): void
    {
        $this->spent('mongo', $microseconds * 1000);
    }

    public function loginAttemptEvent(): void
    {
        $this->incrCounter('login-attempt');
    }

    public function loginSuccessEvent(): void
    {
        $this->incrCounter('login-success');
    }
}