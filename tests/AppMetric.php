<?php

namespace Tests;

use Metric\Metric;

class AppMetric extends Metric
{
    protected $namespace = "myapp";

    public function startMongo(): void
    {
        $this->start('mongo');
    }

    public function spentMongo(int $nanoseconds): void
    {
        $this->spent('mongo', $nanoseconds);
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