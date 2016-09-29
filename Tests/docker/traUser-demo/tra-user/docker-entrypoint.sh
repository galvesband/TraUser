#!/bin/bash
set -e

cd /var/www/traUserBundle
composer install

# If WAIT_FOR_IT variables are present, execute the command
if [ ! -z "$WAIT_FOR_IT_TIMEOUT" ] && [ ! -z "$WAIT_FOR_IT_TARGET" ]; then
        /usr/bin/wait-for-it.sh -t $WAIT_FOR_IT_TIMEOUT $WAIT_FOR_IT_TARGET
fi

cd /var/www/traUserBundle/Tests/test-app/app/config
echo "parameters:" >> parameters.yml
echo "  database_host: db" >> parameters.yml
echo "  database_port: 3306" >> parameters.yml
echo "  database_name: traUser_db" >> parameters.yml
echo "  database_user: traUser_user" >> parameters.yml
echo "  database_password: traUser_pwd" >> parameters.yml
echo "  secret: 'chalkjasofdief'" >> parameters.yml
echo "  mailer_transport: smtp" >> parameters.yml
echo "  mailer_host: 127.0.0.1" >> parameters.yml
echo "  mailer_user: null" >> parameters.yml
echo "  mailer_password: null" >> parameters.yml
echo "  galvesband.tra_user.mail.from: galvesband@gmail.com" >> parameters.yml

exec "$@"
