<?php

use Metric\MyMetric;
use Prometheus\CollectorRegistry;
use Psr\Log\LoggerInterface;

define('APP_START_TIME', hrtime(true));

usleep(10);

$metric = new MyMetric(APP_START_TIME, 'yazoo');

if ($config['persist']) {
    $metric->initStorage($registry);
}

if ($config['debug']) {
    $metric->initLogger($logger);
}
