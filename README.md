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
# HELP myapp_http_duration_seconds Histogram of HTTP request duration
# TYPE myapp_http_duration_seconds histogram
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.01"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.025"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.05"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.1"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.25"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="0.5"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="1"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="2.5"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="5"} 1
myapp_http_duration_seconds_bucket{node="10.0.0.1",status="200",le="+Inf"} 1
myapp_http_duration_seconds_count{node="10.0.0.1",status="200"} 1
myapp_http_duration_seconds_sum{node="10.0.0.1",status="200"} 0.003229
# HELP myapp_http_memory_usage_bytes Memory usage of bytes
# TYPE myapp_http_memory_usage_bytes gauge
myapp_http_memory_usage_bytes{node="10.0.0.1",route="api.books@read"} 5312296
# HELP myapp_http_memory_usage_bytes Histogram of HTTP memory usage in bytes
# TYPE myapp_http_memory_usage_bytes histogram
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="524288"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="1048576"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="1572864"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="2097152"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="2621440"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="3145728"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="3670016"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="4194304"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="4718592"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="5242880"} 0
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="10485760"} 1
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="15728640"} 1
myapp_http_memory_usage_bytes_bucket{node="10.0.0.1",status="200",le="+Inf"} 1
myapp_http_memory_usage_bytes_count{node="10.0.0.1",status="200"} 1
myapp_http_memory_usage_bytes_sum{node="10.0.0.1",status="200"} 5312296
# HELP myapp_http_requests_count Count HTTP requests processed
# TYPE myapp_http_requests_count counter
myapp_http_requests_count{node="10.0.0.1",route="api.books@read",status="200"} 1
# HELP myapp_http_runtime_seconds HTTP request rutime in seconds
# TYPE myapp_http_runtime_seconds gauge
myapp_http_runtime_seconds{node="10.0.0.1",route="api.books@read",timer="mongo"} 0.002551
myapp_http_runtime_seconds{node="10.0.0.1",route="api.books@read",timer="php"} 0.000185
myapp_http_runtime_seconds{node="10.0.0.1",route="api.books@read",timer="php_init"} 2.7E-5
# HELP myapp_signin_attempt_count Count of signin_attempt event
# TYPE myapp_signin_attempt_count counter
myapp_signin_attempt_count{node="10.0.0.1"} 1
```

Grafana dashboard

![Grafana dashboard](https://github.com/demidovich/php-metrics/blob/master/grafana/dashboard.png)
