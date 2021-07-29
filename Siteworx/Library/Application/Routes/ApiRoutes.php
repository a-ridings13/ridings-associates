<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Routes;

use Siteworx\Controllers\Api\ClientController;
use Slim\Routing\RouteCollectorProxy;

/**
 * Class ApiRoutes
 * @package Siteworx\Library\Application\Routes
 */
final class ApiRoutes implements RoutesInterface
{
    /**
     * @param RouteCollectorProxy $routeCollectorProxy
     */
    public static function registerRoutes(RouteCollectorProxy $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('/client', ClientController::class . ':getAction');
    }
}
