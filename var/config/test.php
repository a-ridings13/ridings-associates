<?php

use Psr\Log\LogLevel;
use Siteworx\Library\Email\Transports\LogTransport;

return [

    'settings' => [

        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,

        /** =============================================
         *  Database
         *  ============================================= */
        'db' => [
            'host' => 'mysql',
            'username' => 'root',
            'password' => 'password',
            'database' => 'vagrant',
            'driver' => 'mysql',
            'collation' => 'utf8_general_ci',
            'charset' => 'utf8'
        ],

        'timezone' => 'America/New_York',
        'popup_timeout' => 86400,

        'deployment' => '__DEPLOYMENT__',
        'route_cache' => 'var/cache'

    ],

    'require_ssl' => false,

    'app_name' => 'slim-v4',
    'app_env' => 'vagrant',

    /** =============================================
     *  Run Dir
     *  ============================================= */
    'run_dir' => '__DIR__',

    /** =============================================
     *  Logging
     *  ============================================= */
    'logs' => [
        'log_folder' => '/var/logs',
        'log_level' => LogLevel::DEBUG
    ],

    /** =============================================
     *  Dev Mode
     *  ============================================= */
    'dev_mode' => true,

    /** =============================================
     *  Email
     *  ============================================= */
    'mail' => [
        'admin_email' => 'admin@an-email.com',
        'from_email' => 'no-reply@an-email.com',
        'client_id' => '',
        'client_secret' => '',
        'catch' => true,
        'driver' => LogTransport::class
    ],

    /** =============================================
     *  Site Config
     *  ============================================= */
    'app_url' => 'http://vagrant.local',

    /** =============================================
     *  Crypt
     *  ============================================= */
    'app_key' => '__encryption_key__',
    'encrypt_session' => false,
    'app_salt' => '__app_salt__',

    /** =============================================
     *  Cookies
     *  ============================================= */
    'cookies' => [
        'encrypt' => false,
        'force_insecure' => true
    ],

    /** =============================================
     *  Aws
     *  ============================================= */

    'aws' => [
        'sns' => [
            'credentials' => [
                'key' => '',
                'secret' => ''
            ]
        ],
        's3' => [
            'credentials' => [
                'key' => '',
                'secret' => ''
            ],
            'region' => 'us-east-1',
            'version' => 'latest'
        ],
        'sqs' => [
            'credentials' => [
                'key' => '',
                'secret' => ''
            ],
            'queue' => 'http://goaws:4100/queue/vagrant'
        ]
    ],

    /** =============================================
     *  Memcache
     *  ============================================= */
    'memcache' => [
        'server' => 'localhost',
        'port' => 11211,
        'app_key' => 'slim-v4'
    ],

    /**
     * Whitelisted Consumer Jobs
     */
    'whitelisted_jobs' => []

];
