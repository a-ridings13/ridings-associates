#!/usr/bin/env bash


php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
cp composer.phar /usr/bin/composer
