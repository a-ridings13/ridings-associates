<?php

require 'vendor/autoload.php';

$dotEnv = Dotenv\Dotenv::createMutable(__DIR__);
$dotEnv->load();

$config = require 'var/config/config.php';

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
    ],
    'environments'  => [
        'default_migration_table'   => 'phinxlog',
        'default_database'          => 'app',
        'app'       => [
            'adapter'       => $config['settings']['db']['driver'],
            'host'          => $config['settings']['db']['host'],
            'name'          => $config['settings']['db']['database'],
            'user'          => $config['settings']['db']['username'],
            'pass'          => $config['settings']['db']['password'],
            'charset'       => $config['settings']['db']['charset'],
        ]
    ]
];
