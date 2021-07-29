<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands;

use Siteworx\Library\Application\Core;
use Slim\Routing\Route;

/**
 * Class GetRoutes
 * @package Siteworx\Cli\Commands
 */
final class GetRoutes extends Command
{

    /**
     * @return string
     */
    public static function getHelp(): string
    {
        return 'list registered app routes';
    }

    /**
     * @return int Return exit code
     */
    public function execute(): int
    {
        $routes = Core::routes();

        $routeTable = [];

        $count = 1;

        /** @var Route $route */
        foreach ($routes as $route) {
            $routeTable[] = [
                'count' => $count++,
                'name' => $route->getIdentifier(),
                'endpoint' => $route->getPattern(),
                'methods' => $route->getMethods()[0],
                'controller' => $route->getCallable()
            ];
        }

        $this->cli->table($routeTable);

        return 0;
    }

    /**
     * @return string
     */
    public static function commandSignature(): string
    {
        return 'app-routes';
    }
}
