#!/usr/bin/env bash

NODEV=""
USER="ubuntu"
GROUP="www-data"

if [[ -e $1 ]] ; then
    echo "Available environments are 'production', 'development', 'vagrant' or 'test'"
    exit 1
fi

if ! [[ $1 = "production" || $1 = 'vagrant' || $1 = 'development' || $1 = 'test' ]] ; then
    echo "Available environments are 'production', 'development', 'vagrant' or 'test'"
    exit 1
fi

echo "Deploying to the $1 environment"

if  [[ $1 = "production" ]] ; then
    USER="www-data"
    GROUP="www-data"
    RUNDIR="/var/www/html"
    NODEV="--no-dev"
    NODE_ENV="production"
fi

if [[ $1 = "vagrant" ]] ; then
    RUNDIR="/home/vagrant/Code"
    USER="vagrant"
    GROUP="www-data"
    NODE_ENV="development"
fi

if [[ $1 = 'development' ]] ; then
    USER="www-data"
    GROUP="www-data"
    RUNDIR='/var/www/html'
    NODEV="--no-dev"
    NODE_ENV="production"
fi

if [[ $1 = 'test' ]] ; then
    RUNDIR=$(pwd)
    USER="www-data"
    GROUP="www-data"
    NODE_ENV="development"
fi


echo "**************************"
echo "    Composer Install      "
echo "**************************"
composer install ${NODEV}


echo "**************************"
echo "    Creating Folders      "
echo "**************************"
if [[ ! -d ./var/logs ]] ; then
    mkdir ./var/logs
    touch ./var/logs/app.log
    touch ./var/logs/queue.log
    touch ./var/logs/cron.log
    touch ./var/logs/beanstalk.log
fi

if [[ ! -d ./var/cache ]] ; then
    mkdir ./var/cache
fi

echo "**************************"
echo "    Setting Config        "
echo "**************************"
cp var/config/$1.php var/config/config.php
echo $(date +%s) > .epoch

if [[ ! -e ./cli ]] ; then
    ln -s ./Siteworx/Cli/bin/app
fi

ESCRUNDIR=$(echo "$RUNDIR" | sed 's/\//\\\//g')

if  [[ $1 = "production" ]] ; then
echo "**************************"
echo "  Setting Up Production   "
echo "**************************"

    build=`cat build.txt`
    sed -i -e "s/__DEPLOYMENT__/$build/g" var/config/config.php

    npm install
    npm run ${NODE_ENV}
    rm -Rf node_modules

echo "**************************"
echo "       Moving Code        "
echo "**************************"

    rm -Rf ${RUNDIR}/*
    cp -Rp ./* ${RUNDIR}

    cd ${RUNDIR}
    ./app --generate-resources

echo "**************************"
echo "   Setting up Supervisor  "
echo "**************************"
    sudo php -r "file_put_contents('/etc/supervisor/conf.d/supervisor.conf', str_replace('__RUN_DIR__', '$RUNDIR', file_get_contents('$RUNDIR/var/etc/supervisor/supervisor.conf')));"
    sudo php -r "file_put_contents('/etc/supervisor/conf.d/supervisor.conf', str_replace('__USER__', '$USER', file_get_contents('/etc/supervisor/conf.d/supervisor.conf')));"
    sudo /etc/init.d/supervisor restart
    sleep 3
    sudo /etc/init.d/supervisor status
    sudo supervisorctl status

echo "**************************"
echo "     Setting up cron      "
echo "**************************"
    cp var/etc/cron.d/cron.disabled /etc/cron.d/app_cron
fi

if  [[ $1 = "development" ]] ; then
echo "**************************"
echo "    Setting Up Sandbox    "
echo "**************************"

    build=`cat build.txt`
    sed -i -e "s/__DEPLOYMENT__/$build/g" var/config/config.php

    npm install
    npm run ${NODE_ENV}
    rm -Rf node_modules

echo "**************************"
echo "       Moving Code        "
echo "**************************"

    rm -Rf ${RUNDIR}/*
    cp -Rp ./* ${RUNDIR}

    ./app --generate-resources
fi

if [[ $1 = "vagrant" ]] ; then
    sed -i -e "s/__DEPLOYMENT__/Vagrant Deployment/g" var/config/config.php
    sudo php -r "file_put_contents('/etc/supervisor/conf.d/supervisor.conf', str_replace('__RUN_DIR__', '$RUNDIR', file_get_contents('$RUNDIR/var/etc/supervisor/supervisor.conf')));"
    sudo php -r "file_put_contents('/etc/supervisor/conf.d/supervisor.conf', str_replace('__USER__', '$USER', file_get_contents('/etc/supervisor/conf.d/supervisor.conf')));"
    sudo /etc/init.d/supervisor restart
fi

if [[ $1 = 'test' ]] ; then
echo "**************************"
echo "         Testing          "
echo "**************************"
    php -r "file_put_contents('var/config/config.php', str_replace('__DIR__', '$PWD', file_get_contents('var/config/config.php')));"
    sudo npm install
    npm run ${NODE_ENV}
    chmod +x bin/tests_prepare.sh
    bin/tests_prepare.sh
fi

echo "**************************"
echo "    Setting Permissions   "
echo "**************************"
sudo chown -R ${USER}:${GROUP} ${RUNDIR}
sudo chmod -R 750 ${RUNDIR}
chmod 777 -R ${RUNDIR}/var/logs
chmod 777 -R ${RUNDIR}/var/logs/*
chmod 777 -R ${RUNDIR}/var/cache

touch .env

vendor/bin/phinx migrate
