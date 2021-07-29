#!/usr/bin/env bash

cd $HOME
FIRST_TIME=0

function installDefaults() {

    echo "Installing Default Packages"

    if [[ -e /usr/bin/php ]] ; then
        echo "Already done! Skipping..."
        return
    fi

    sudo add-apt-repository ppa:ondrej/php

    sudo apt-get update

    sudo apt-get install -yq \
    apache2 \
    php7.4 \
    php-memcached \
    php7.4-dev \
    libapache2-mod-php7.4 \
    php7.4-gd \
    php7.4-curl \
    php7.4-json \
    php7.4-xml \
    php7.4-intl \
    php7.4-mbstring \
    php7.4-mysql \
    php7.4-zip \
    php7.4-bz2 \
    supervisor \
    build-essential \
    memcached \
    unzip \
    awscli \
    ntpdate

    sudo ntpdate -u ntp.ubuntu.com

    echo "<VirtualHost *:80>
    ServerAdmin webmaster@vagrant.com
    ServerName vagrant.local
    DocumentRoot /home/vagrant/Code/public
    <Directory /home/vagrant/Code/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>

    SetEnvIf Request_URI '.(gif)|(jpg)|(jpeg)|(png)|(ico)|(css)|(js)$' exclude_from_log
    CustomLog /var/log/apache2/vagrant_access.log vhost_combined env=!exclude_from_log
    ErrorLog /var/log/apache2/vagrant_error.log
</VirtualHost>
" | sudo tee /etc/apache2/sites-available/vagrant.conf

    sudo a2ensite vagrant

    cp /home/vagrant/Code/var/etc/apache/apache2.conf /etc/apache2/apache2.conf

    sed -i "s/www-data/vagrant/" /etc/apache2/envvars
    sudo a2enmod rewrite
    sudo a2enmod headers

    rm /var/www/html/index.html
    echo "<?php phpinfo();" > /var/www/html/index.php

    sudo service apache2 restart

    echo "127.0.0.1 vagrant.local" | sudo tee -a /etc/hosts

    echo "source ~/Code/bin/vagrant_bash_alias.sh"  | tee -a /home/vagrant/.bashrc
}

function installComposer() {
    echo "Installing Composer"

    if [[ -e /usr/bin/composer ]] ; then
        echo "Already done! Skipping..."
        return
    fi

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo cp composer.phar /usr/bin/composer
}

function installXdebug() {

    echo "Installing xDebug"

    if [[ -e /usr/lib/php/20180731/xdebug.so ]] ; then
        echo "Already done! Skipping..."
        return
    fi

    wget https://github.com/xdebug/xdebug/archive/2.9.2.tar.gz
    tar -xvf 2.9.2.tar.gz
    cd xdebug-2.9.2
    phpize
    ./configure --enable-xdebug
    make
    sudo make install
    echo "
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_host=10.0.2.2
xdebug.remote_port=9000
xdebug.remote_connect_back=1" | tee -a /etc/php/7.4/mods-available/xdebug.ini

    sudo phpenmod xdebug
    echo "export XDEBUG_CONFIG='idekey=PhpStorm1'" | tee -a /home/vagrant/.bashrc
    sudo service apache2 restart

}

function installMysql() {

    echo "Installing xDebug"

    if [[ -e /usr/bin/mysql ]] ; then
        echo "Already done! Skipping..."
        return
    fi

    FIRST_TIME=1

    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password password'
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password password'
    sudo apt-get -y install mysql-server

    sed -i "s/127.0.0.1/0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
    echo "
general_log_file        = /var/log/mysql/mysql.log
general_log             = 1" | tee -a /etc/mysql/mysql.conf.d/mysqld.cnf
    mysql -uroot -ppassword mysql -e "update user set Host = '%' where user = 'root';"

    /etc/init.d/mysql restart

    mysql -uroot -ppassword -e "create database vagrant"
    mysql -uroot -ppassword vagrant < /home/vagrant/Code/migrations/seed/seed.sql

}

function installSqs () {

    if [[ ! -e $(docker ps -a | grep goaws) ]] ; then
         echo "restarting goaws"
         docker stop goaws
         docker rm goaws
         sudo docker run -d -p 4100:4100 --name goaws siteworxpro/goaws
         aws --endpoint-url http://localhost:4100 sqs create-queue --queue-name worksmart --region us-east-1
         return
    fi

    sudo apt-get install -yq awscli

    if [[ -e /home/vagrant/.aws ]] ; then
        mkdir /home/vagrant/.aws
        touch /home/vagrant/.aws/credentials

        echo "
        [default]
        aws_access_key_id = xxx
        aws_secret_access_key = xxx" | sudo tee -a /home/vagrant/.aws/credentials
    fi

    if [[ -e /root/.aws ]] ; then
        sudo mkdir /root/.aws
        sudo touch /root/.aws/credentials

        echo "
        [default]
        aws_access_key_id = xxx
        aws_secret_access_key = xxx" | sudo tee -a /root/.aws/credentials
    fi

    sudo docker run -d -p 4100:4100 --name goaws siteworxpro/goaws
    aws --endpoint-url http://localhost:4100 sqs create-queue --queue-name worksmart --region us-east-1

}

function installDocker() {

    if [[ ! -f /usr/bin/docker ]] ; then
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
        sudo add-apt-repository \
           "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
           $(lsb_release -cs) \
           stable"
        sudo apt-get update
        sudo apt-get -yq install docker-ce
        sudo usermod -aG docker vagrant
    else
        echo "Docker already installed!"
    fi

}

function firstTimeSetup() {
    ./app --generate-key --write
    ./app --generate-oauth-client -c test -g authorization_code -i --write
    ./app --add-client-redirect-uri -c 1 -u https://www.postman.com/oauth2/callback
    ./app --add-client-redirect-uri -c 1 -u http://vagrant.local/api/oauth/authorize
}

installDefaults
installComposer
installMysql
installXdebug
installDocker
installSqs

cd /home/vagrant/Code
chmod +x ./bin/deploy.sh
chmod +x ./bin/vagrant_bash_alias.sh
sudo ./bin/deploy.sh vagrant

if [[ ${FIRST_TIME} ]] ; then
    firstTimeSetup
fi
