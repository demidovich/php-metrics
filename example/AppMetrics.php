<?php

use Metrics\Metrics;

/**
 * Wrappers of your counters and timers
 */
class AppMetrics extends Metrics
{
    protected $namespace = 'myapp';

    /**
     * Register spent time from laravel database query event
     * 
     * @param int $milliseconds
     * @return void
     */
    public function spentSql(int $milliseconds): void
    {
        $this->runtime()->spent('sql', (int) $milliseconds * 1e6);
    }

    /**
     * Start timer of redis query
     * 
     * @return void
     */
    public function startRedis(): void
    {
        $this->runtime()->start('redis');
    }

    /**
     * Start timer of remote call
     * 
     * @return void
     */
    public function startRemoteCall(): void
    {
        $this->runtime()->start('remote_call');
    }

    /**
     * Increase of sigin attempts counter
     * 
     * @return void
     */
    public function registerSigninAttempt(): void
    {
        $this->counters()->increase('signin_attempt');
    }
}