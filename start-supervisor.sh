#!/bin/bash

echo "Creating supervisor config for user www..."
cat > /etc/supervisor/conf.d/laravel-worker.conf << EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/local/bin/php /var/www/artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3
directory=/var/www
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/queue-worker.log
stderr_logfile=/var/www/storage/logs/queue-worker-error.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=2
stopwaitsecs=3600
user=www
environment=HOME="/home/www",USER="www"
EOF

echo "Starting supervisord..."

cat > /tmp/supervisord.conf << 'EOF'
[unix_http_server]
file=/tmp/supervisor.sock

[supervisord]
logfile=/var/www/storage/logs/supervisord.log
pidfile=/tmp/supervisord.pid
nodaemon=true

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix:///tmp/supervisor.sock

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/local/bin/php /var/www/artisan queue:work --verbose --tries=3 --timeout=90 --sleep=3
directory=/var/www
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/queue-worker.log
stderr_logfile=/var/www/storage/logs/queue-worker-error.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=2
stopwaitsecs=3600
user=www
environment=HOME="/home/www",USER="www"
EOF

/usr/bin/supervisord -n -c /tmp/supervisord.conf
