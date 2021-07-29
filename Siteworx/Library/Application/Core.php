<?php

declare(strict_types=1);

namespace Siteworx\Library\Application;

use Dotenv\Dotenv;
use Siteworx\Library\Application\Handlers\{ErrorHandler, NotAuthorizedHandler, NotFoundHandler, ShutdownHandler};
use Siteworx\Library\Http\Exceptions\{NotAuthorizedException, NotFoundException};
use Siteworx\Library\Application\Routes\{ApiRoutes, PublicAjax, PublicRoutes};
use Siteworx\Controllers\Api\OAuth\AccessTokenController;
use Siteworx\Controllers\Api\OAuth\AuthorizeController;
use Siteworx\Library\Http\Request;
use Siteworx\Middleware\{ApiMiddleware,
    OAuthMiddleware,
    RateLimitMiddleware,
    SanitizationMiddleware,
    ValidationMiddleware};
use Slim\App;
use Slim\Exception\{HttpMethodNotAllowedException, HttpNotFoundException};
use Slim\Interfaces\RouteInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Routing\{Route, RouteCollectorProxy};

/**
 * Class App
 *
 * @method Container getContainer()
 *
 * @package Siteworx\Library\Application
 */
final class Core extends App
{

    /**
     * @var Container
     */
    protected $container;

    public static function factory(): self
    {
        $dotEnv = Dotenv::createMutable(__DIR__ . '/../../../');
        $dotEnv->load();

        $container = new Container();
        $app = new static($container->response, $container);
        $errorMiddleware = new ErrorMiddleware(
            $app->callableResolver,
            $container->response,
            (bool) $container->config->get('dev_mode', false),
            true,
            true
        );

        $errorMiddleware->setErrorHandler(NotFoundException::class, NotFoundHandler::class);
        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, NotFoundHandler::class);
        $errorMiddleware->setErrorHandler(NotAuthorizedException::class, NotAuthorizedHandler::class);
        $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, NotFoundHandler::class);
        $errorMiddleware->setDefaultErrorHandler(ErrorHandler::class);
        $app->add($errorMiddleware);

        register_shutdown_function(new ShutdownHandler());

        $app->registerRoutes();

        return $app;
    }

    /**
     * @return Core
     */
    private static function app(): self
    {
        return $GLOBALS['app'];
    }

    /**
     * @return Container
     */
    public static function di(): Container
    {
        if (!isset($GLOBALS['app'])) {
            return new Container();
        }

        return self::app()->getAppContainer();
    }

    /**
     * @return Container
     */
    private function getAppContainer(): Container
    {
        return $this->container;
    }

    private function registerRoutes(): void
    {
        $this->registerPublicWeb();
        $this->registerPublicAjax();
        $this->registerApi();

        /**
         * Global Middleware
         */
        $this->add(new SanitizationMiddleware());
        $this->add(new ValidationMiddleware());
    }

    public function registerPublicWeb(): void
    {
        $this->group('', function (RouteCollectorProxy $routeCollectorProxy) {
            PublicRoutes::registerRoutes($routeCollectorProxy);
        });
    }

    public function registerPublicAjax(): void
    {
        $this->group('/ajax', function (RouteCollectorProxy $routeCollectorProxy) {
            PublicAjax::registerRoutes($routeCollectorProxy);
        })->add(new ApiMiddleware());
    }

    public function registerApi(): void
    {
        $this->group('/api', function (RouteCollectorProxy $routeCollectorProxy) {
            ApiRoutes::registerRoutes($routeCollectorProxy);
        })
            ->add(RateLimitMiddleware::createDefault())
            ->add(new ApiMiddleware())
            ->add(new OAuthMiddleware());

        $this->group('/api/oauth', function (RouteCollectorProxy $routeCollectorProxy) {
            $routeCollectorProxy->post('/access_token', AccessTokenController::class . ':postAction');
            $routeCollectorProxy->post('/authorize', AuthorizeController::class . ':postAction');
            $routeCollectorProxy->get('/authorize', AuthorizeController::class . ':getAction');
        });
    }

    /**
     * @return array
     */
    public static function routes(): array
    {
        return self::app()->getRouteCollector()->getRoutes();
    }

    /**
     * @param Request $request
     * @return RouteInterface|Route
     */
    public static function getRoute(Request $request): ?Route
    {
        $routeResult = self::app()
            ->getRouteResolver()
            ->computeRoutingResults($request->getUri()->getPath(), $request->getMethod());

        if ($routeResult->getRouteIdentifier() === null) {
            return null;
        }

        return self::app()
            ->getRouteResolver()
            ->resolveRoute($routeResult->getRouteIdentifier());
    }

    public static function getResource(Request $request): ?string
    {
        $route = self::getRoute($request);

        if ($route === null) {
            return null;
        }

        return strtolower(str_replace(['\\', ':'], '.', $route->getCallable()));
    }

    public static function getCallable(Request $request): string
    {
        $route = self::getRoute($request);

        return $route ? $route->getCallable() : '';
    }
}
