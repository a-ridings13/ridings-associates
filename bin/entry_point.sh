#!/usr/bin/env bash

function fetchFromSsm {
    echo "Fetching environment param from SSM myapp.${APP_ENV}.${1}"
    PARAMETER_VALUE=$(aws ssm get-parameter --name myapp.${APP_ENV}.${1} --with-decryption | jq .Parameter.Value --raw-output)
    echo ${1}=${PARAMETER_VALUE} | tee -a .env
}

RUNDIR=$(pwd)
USER='www-data'
GROUP='www-data'


if [[ -z ${APP_ENV+x} ]] ; then
    echo "APP_ENV is required"
    exit 1
fi

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

echo $(date +%s) > .epoch

if [[ ! -e ./cli ]] ; then
    ln -s ./Siteworx/Cli/bin/app
fi

if [[ ${DEV_MODE} == 1 ]] ; then
echo "**************************"
echo "       IN DEV MODE        "
echo "**************************"

echo "*****************************"
echo " Installing dev dependencies "
echo "*****************************"
    ## install missing dev dependencies
    composer install

    npm install
    npm run development
fi

echo "**************************"
echo "    Setting Permissions   "
echo "**************************"
sudo chown -R ${USER}:${GROUP} ${RUNDIR}
sudo chmod -R 750 ${RUNDIR}
chmod 777 -R ${RUNDIR}/var/logs
chmod 777 -R ${RUNDIR}/var/logs/*
chmod 777 -R ${RUNDIR}/var/cache

build=`cat build.txt`
sed -i -e "s/__DEPLOYMENT__/$build/g" var/config/config.php

echo "**************************"
echo "    Setting Parameters    "
echo "**************************"

PARAMETERS=("DB_HOST" "DB_USERNAME" "DB_PASSWORD" "DB_DATABASE" "AWS_KEY" \
"AWS_SECRET" "MAIL_API_ID" "MAIL_API_SECRET" "AWS_KEY" "AWS_SECRET" "BRAINTREE_ENV" "BRAINTREE_MERCHANT_ID" \
"BRAINTREE_MERCHANT_KEY" "BRAINTREE_MERCHANT_SECRET" "APP_KEY" "APP_SALT"
)

if [[ ! -z ${AWS_CREDENTIALS+x} ]] ; then
    AWS_SECRET=$(echo ${AWS_CREDENTIALS} | jq .secret --raw-output)
    AWS_KEY=$(echo ${AWS_CREDENTIALS} | jq .key --raw-output)

    echo "
[default]
aws_access_key_id = ${AWS_KEY}
aws_secret_access_key = ${AWS_SECRET}" | sudo tee -a /root/.aws/credentials

    echo "
[default]
region = us-east-1
output = json" | sudo tee -a /root/.aws/config
fi

for PARAMETER in "${PARAMETERS[@]}"
do :
    if [[ -z ${!PARAMETER+x} ]] ; then
        fetchFromSsm ${PARAMETER}
    fi
done

echo "**************************"
echo "    Migrating Database    "
echo "**************************"

vendor/bin/phinx migrate

tail -f var/logs/* &

if [[ ${CRON} == 1 ]] ; then
    echo "**************************"
    echo "     I'm a cron server    "
    echo "**************************"
    cp var/etc/cron.d/cron.disabled /etc/cron.d/cron
    service cron start
    wait 2
    service cron status
fi

if [[ ${WORKER} == 1 ]] ; then
    echo "**************************"
    echo "    I'm a good worker     "
    echo "**************************"
     ./Siteworx/Cli/bin/app --start-consumer
else
    apache2-foreground
fi
