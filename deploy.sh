#!/bin/bash

cd /var/www/tontine/server.tontine.plus.2.0

composer install
composer dump-autoload

DIR="vendor/laravel/passport/database/migrations"

if [ "$(ls -A $DIR)" ]; then
    rm -rf $DIR/*
fi


FILE=bootstrap/cache/routes.php
if [ -f "$FILE" ]; then
    rm bootstrap/cache/routes.php
fi


php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear
