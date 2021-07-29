<?php

declare(strict_types=1);

require '../vendor/autoload.php';

use Siteworx\Library\Application\Core;
use Whoops\Handler\PrettyPageHandler;


/*
|--------------------------------------------------------------------------
| Get Ready....
|--------------------------------------------------------------------------
*/
$app = Core::factory();

if (
    (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) &&
    $app->getContainer()->config->get('require_ssl', true) === true
) {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url);
    exit;
}

/*
|--------------------------------------------------------------------------
| Go.....
|--------------------------------------------------------------------------
*/
try {
    $app->run($app->getContainer()->request);
} catch (Exception $exception) {
    $app->getContainer()->log->emergency(
        $exception->getMessage() . ' in file ' .
        $exception->getFile() . ' on line ' . $exception->getLine()
    );

    if ($app->getContainer()->config->get('dev_mode')) {
        $handler = new PrettyPageHandler();
        $whoops = new Whoops\Run();
        $whoops->appendHandler($handler);
        $whoops->handleException($exception);
        exit;
    }

    echo 'Server Error.';
}
