#!/usr/bin/env bash

mkdir /var/log/supervisor

/usr/bin/supervisord

php-fpm -F
