<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Routes;

use Siteworx\Controllers\Web\IndexController;
use Slim\Routing\RouteCollectorProxy;

/**
 * Class PublicRoutes
 * @package Siteworx\Library\Application\Routes
 */
final class PublicRoutes implements RoutesInterface
{
    /**
     * @param RouteCollectorProxy $routeCollectorProxy
     */
    public static function registerRoutes(RouteCollectorProxy $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('[/]', IndexController::class . ':getAction');
    }
}
