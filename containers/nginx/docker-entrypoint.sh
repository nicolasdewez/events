#!/bin/bash

if [ -z "$FPM_HOST" ]; then PHP_FPM_HOST="php"; else PHP_FPM_HOST=$FPM_HOST; fi

echo "upstream php-upstream { server $PHP_FPM_HOST:9000; }" > /etc/nginx/conf.d/upstream.conf

exec "$@"
