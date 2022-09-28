#!/bin/bash
echo 'The earth says hello'
/usr/local/bin/php /app/artisan migrate:up
/usr/local/bin/php /app/artisan migrate
/usr/local/bin/php /app/artisan optimize
