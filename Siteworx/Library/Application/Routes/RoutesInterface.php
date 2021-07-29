<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Routes;

use Slim\Routing\RouteCollectorProxy;

/**
 * Interface RoutesInterface
 * @package Siteworx\Library\Application\Routes
 */
interface RoutesInterface
{
    public static function registerRoutes(RouteCollectorProxy $routeCollectorProxy): void;
}
