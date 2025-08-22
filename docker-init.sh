#!/bin/bash

echo "API..."

echo "MySQL..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL ready!"

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY..."
    php artisan key:generate --no-interaction
fi

php artisan config:clear
php artisan cache:clear

echo "migrate"
php artisan migrate:fresh --seed --force

echo "Success"

echo "Starting supervisor for queue worker..."
/usr/local/bin/start-supervisor.sh &

echo "scheduler..."
nohup php artisan schedule:work > /dev/null 2>&1 &

php artisan serve --host=0.0.0.0 --port=8000
