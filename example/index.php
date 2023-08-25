<?php

define("APP_START_TIME", hrtime(true));

require __DIR__."/../vendor/autoload.php";
require __DIR__."/AppMetrics.php";

$metrics = new AppMetrics(APP_START_TIME, ["app_node" => "10.0.0.1"]);
$metrics->initStorage(
    Metrics\Storage::create("redis", ["host" => "redis"])
);

// PHP initialization completed
// Start of business logic 
$metrics->startPhp();

switch ($_SERVER["REQUEST_URI"]) {

    case "/":
        $metrics->setHttpMethod("get");
        $metrics->setHttpRoute("index@index");
        echo "index";
        $metrics->setHttpStatus(200);
        break;

    case "/books":
        $metrics->setHttpMethod("get");
        $metrics->setHttpRoute("api.books@read");
        echo appApiResourceReadHandler($metrics);
        $metrics->setHttpStatus(200);
        break;

    case "/metrics":
        $metrics->setHttpMethod("get");
        $metrics->setHttpRoute("metrics");
        header("Content-Type: text/plain");
        echo exportMetricsHandler($metrics);
        $metrics->setHttpStatus(200);
        break;

    default:
        $metrics->setHttpMethod("get");
        $metrics->setHttpRoute("error");
        $metrics->setHttpStatus(404);

}

function appApiResourceReadHandler(AppMetrics $metrics)
{
    // Register runtime for redis query
    $metrics->startRedis();
    usleep(1000);

    // Some business logic
    $metrics->startPhp();
    usleep(1000);

    // Register runtime for remote call
    $metrics->startRemoteCall();
    usleep(1000);

    // Some business logic
    $metrics->startPhp();
    usleep(1000);

    // Adding the spent time
    // E.g. inside the database query event
    // These 500 microseconds will be subtracted from the 1000 microseconds 
    // of the current php tracking
    $metrics->spentSql(500);

    // Register a business logic event
    $metrics->registerSigninAttempt();

    // If Metrics\Storage has been initialized all metrics will be persisted 
    // with register_shutdown_function()

    return "books";
}

function exportMetricsHandler(AppMetrics $metrics)
{
    echo $metrics->storage()->fetch();
}