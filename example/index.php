<?php

define('APP_START_TIME', hrtime(true));

require __DIR__.'/../vendor/autoload.php';

$metric = new MyMetrics(APP_START_TIME, [
    'route'  => 'my-route',
    'method' => 'get',
    'host'   => '10.0.0.1',
]);

$storage = Metrics\Storage::create('in-memory', [], $metric);

// PHP initialization is complete.
// Start of business logic 
$metric->startPhp();

if ($_SERVER['REQUEST_URI'] == '/metrics') {
    echo exportMetricsHandler($metric);
} else {
    echo appHandler($metric);
}

function appHandler(MyMetrics $metric)
{
    // Register runtime for redis query
    $metric->startRedis();
    usleep(1000);

    // Some business logic
    $metric->startPhp();
    usleep(1000);

    // Register runtime for remote call
    $metric->startRemoteCall();
    usleep(1000);

    // Some business logic
    $metric->startPhp();
    usleep(1000);

    // Adding the spent time
    // E.g. inside the database query event
    // These 500 microseconds will be subtracted from the 1000 microseconds 
    // of the current php tracking
    $metric->spentSql(500);

    // Register a business logic event
    $metric->registerSigninAttempt();

    // If Metrics\Storage has been initialized all metrics will be persisted 
    // with register_shutdown_function()
}

function exportMetricsHandler(Metrics\Storage $storage)
{
    echo $storage->fetch();
}