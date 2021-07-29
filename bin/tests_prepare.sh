#!/usr/bin/env bash

sudo mkdir /root/.aws
sudo touch /root/.aws/config

echo "
[default]
aws_access_key_id = xxx
aws_secret_access_key = xxx
" | sudo tee -a /root/.aws/config
aws --endpoint-url http://goaws:4100 sqs create-queue --queue-name vagrant --region us-east-1

echo "Seeding Database"
mysql -uroot -ppassword -hmysql -e "CREATE SCHEMA vagrant"
#mysql -uroot -ppassword -hmariadb tests < migrations/seed/seed.sql
build=`cat build.txt`

echo "Setting Config"
sed -i -e "s/__DEPLOYMENT__/Testing/g" var/config/config.php
IP=$(ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')
sed -i -e "s/__IP__/${IP}/g" tests/acceptance.suite.yml

echo "Setting up Apache"
rm -R /var/www/html
ln -s $(pwd) /var/www/html
cp var/etc/apache/apache2.conf /etc/apache2/apache2.conf
cp var/etc/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
a2enmod rewrite
service apache2 start
echo 'Starting Consumer'
PHP_EXEC=$(which php)
php -r "file_put_contents('./Siteworx/Cli/bin/app', str_replace('#!/usr/bin/php', '#!${PHP_EXEC}', file_get_contents('./Siteworx/Cli/bin/app')));"

Siteworx/Cli/bin/app --start-consumer &

touch .env
