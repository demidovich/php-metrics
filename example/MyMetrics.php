<?php

use Metrics\Metrics;

/**
 * Wrappers of your counters and timers
 */
class MyMetrics extends Metrics
{
    protected $namespace = 'myapp';

    public function spentSql(int $microseconds): void
    {
        $this->runtime()->spent('sql', (int) $microseconds * 1000);
    }

    public function startRedis(): void
    {
        $this->runtime()->start('redis');
    }

    public function startRemoteCall(): void
    {
        $this->runtime()->start('remote_call');
    }

    public function registerSigninAttempt(): void
    {
        $this->counters()->increase('signin_attempt');
    }
}