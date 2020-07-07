[![Build Status](https://travis-ci.com/demidovich/php-metrics.svg?branch=master)](https://travis-ci.com/demidovich/php-metrics) [![codecov](https://codecov.io/gh/demidovich/php-metrics/branch/master/graph/badge.svg)](https://codecov.io/gh/demidovich/php-metrics)

## php metrics

Install

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