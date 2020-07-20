[![Build Status](https://travis-ci.com/demidovich/php-metrics.svg?branch=master)](https://travis-ci.com/demidovich/php-metrics) [![codecov](https://codecov.io/gh/demidovich/php-metrics/branch/master/graph/badge.svg)](https://codecov.io/gh/demidovich/php-metrics)

## php metrics

This project is a framework agnostic simple library for monitoring PHP application in the Prometheus. 

Start containers and install vendors

```
make up
./composer install
```

Visit

```
http://localhost:8090/
http://localhost:8090/books
```

Check metrics

```
http://localhost:8090/metrics
```

```
# HELP app_http_duration_seconds Histogram of HTTP request duration
# TYPE app_http_duration_seconds histogram
app_http_duration_seconds_bucket{method="GET",status="200",le="0.01"} 155
app_http_duration_seconds_bucket{method="GET",status="200",le="0.025"} 1482
app_http_duration_seconds_bucket{method="GET",status="200",le="0.05"} 83757
app_http_duration_seconds_bucket{method="GET",status="200",le="0.1"} 101800
app_http_duration_seconds_bucket{method="GET",status="200",le="0.25"} 102859
app_http_duration_seconds_bucket{method="GET",status="200",le="0.5"} 102934
app_http_duration_seconds_bucket{method="GET",status="200",le="1"} 102935
app_http_duration_seconds_bucket{method="GET",status="200",le="2.5"} 102935
app_http_duration_seconds_bucket{method="GET",status="200",le="5"} 102935
app_http_duration_seconds_bucket{method="GET",status="200",le="+Inf"} 102935
app_http_duration_seconds_count{method="GET",status="200"} 102935
app_http_duration_seconds_sum{method="GET",status="200"} 4344.31459800000001659
# HELP myapp_http_memory_usage_bytes Memory usage of bytes
# TYPE myapp_http_memory_usage_bytes gauge
myapp_http_memory_usage_bytes{app_node="10.0.0.1",route="api.books@read"} 389944
myapp_http_memory_usage_bytes{app_node="10.0.0.1",route="index@index"} 523664
myapp_http_memory_usage_bytes{app_node="10.0.0.1",route="metrics"} 392384
# HELP myapp_http_requests_total Total HTTP requests processed
# TYPE myapp_http_requests_total counter
myapp_http_requests_total{app_node="10.0.0.1",route="api.books@read",method="get"} 3
myapp_http_requests_total{app_node="10.0.0.1",route="index@index",method="get"} 1
myapp_http_requests_total{app_node="10.0.0.1",route="metrics",method="get"} 7
# HELP myapp_http_runtime_seconds HTTP request rutime in seconds
# TYPE myapp_http_runtime_seconds gauge
myapp_http_runtime_seconds{app_node="10.0.0.1",route="api.books@read",timer="php"} 0.005875
myapp_http_runtime_seconds{app_node="10.0.0.1",route="api.books@read",timer="php_init"} 0.000506
myapp_http_runtime_seconds{app_node="10.0.0.1",route="api.books@read",timer="redis"} 0.00108
myapp_http_runtime_seconds{app_node="10.0.0.1",route="api.books@read",timer="remote_call"} 0.001154
myapp_http_runtime_seconds{app_node="10.0.0.1",route="api.books@read",timer="sql"} 0.0005
myapp_http_runtime_seconds{app_node="10.0.0.1",route="index@index",timer="php"} 0.006504
myapp_http_runtime_seconds{app_node="10.0.0.1",route="index@index",timer="php_init"} 0.159591
myapp_http_runtime_seconds{app_node="10.0.0.1",route="metrics",timer="php"} 0.003691
myapp_http_runtime_seconds{app_node="10.0.0.1",route="metrics",timer="php_init"} 0.000421
# HELP myapp_http_runtime_seconds_total HTTP request rutime total in seconds
# TYPE myapp_http_runtime_seconds_total gauge
myapp_http_runtime_seconds_total{app_node="10.0.0.1",route="api.books@read"} 0.011963
myapp_http_runtime_seconds_total{app_node="10.0.0.1",route="index@index"} 0.167348
myapp_http_runtime_seconds_total{app_node="10.0.0.1",route="metrics"} 0.005155
# HELP myapp_http_statuses_total Total HTTP response statuses
# TYPE myapp_http_statuses_total counter
myapp_http_statuses_total{app_node="10.0.0.1",route="api.books@read",status="200"} 3
myapp_http_statuses_total{app_node="10.0.0.1",route="index@index",status="200"} 1
myapp_http_statuses_total{app_node="10.0.0.1",route="metrics",status="200"} 7
# HELP myapp_signin_attempt_total Count of signin_attempt event
# TYPE myapp_signin_attempt_total counter
myapp_signin_attempt_total{app_node="10.0.0.1"} 3
```