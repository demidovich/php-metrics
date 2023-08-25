<?php

namespace Metrics;

use Metrics\Metrics;
use Psr\Log\LoggerInterface;

class Debug
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function toLog(Metrics $metrics): void
    {
        $namespace = $metrics->namespace();
        $memory    = $metrics->memoryUsage();
        $labels    = $metrics->labels()->all();
        $counters  = $metrics->counters()->all();
        $timers    = $metrics->runtime()->fetchResultsInSeconds();

        $timers["total"] = array_sum($timers);

        $debug  = PHP_EOL;
        $debug .= "#" . PHP_EOL;
        $debug .= "# {$namespace} metrics" . PHP_EOL;
        $debug .= "#" . PHP_EOL;
        $debug .= "method : " . $metrics->httpMethod() . PHP_EOL;
        $debug .= "route  : " . $metrics->httpRoute() . PHP_EOL;
        $debug .= "status : " . $metrics->httpStatus() . PHP_EOL;
        $debug .= "memory : " . round($memory / (1024 * 1024), 2) . "Mb" . PHP_EOL;
        $debug .= "labels : " . print_r($labels, true) . PHP_EOL;
        $debug .= "timers : " . print_r($timers, true) . PHP_EOL;
        $debug .= "counters : " . print_r($counters, true) . PHP_EOL;
        $debug .= PHP_EOL;

        $this->logger->info($debug);
    }
}
