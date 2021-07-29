<?php

declare(strict_types=1);

namespace Siteworx\Controllers;

use Siteworx\Library\Http\{Exceptions\NotFoundException, Request, Response};
use League\Tactician\CommandBus;
use Monolog\Logger;
use Noodlehaus\Config;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Application\Exceptions\InvalidContainerItemException;
use Siteworx\Library\Session\Session;
use Siteworx\Library\Twig;

/**
 * Class Controller
 * @property Twig view
 * @property CommandBus commandBus
 * @property Session session
 * @property Logger log
 * @property Config config
 */
abstract class Controller implements ControllerInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws NotFoundException
     */
    public function getAction(Request $request, Response $response, array $params = []): Response
    {
        throw new NotFoundException($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws NotFoundException
     */
    public function postAction(Request $request, Response $response, array $params = []): Response
    {
        throw new NotFoundException($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws NotFoundException
     */
    public function putAction(Request $request, Response $response, array $params = []): Response
    {
        throw new NotFoundException($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws NotFoundException
     */
    public function deleteAction(Request $request, Response $response, array $params = []): Response
    {
        throw new NotFoundException($request, $response);
    }

    public function __get($name)
    {
        return Core::di()->$name;
    }

    public function __set($name, $value)
    {
        throw new \RuntimeException('Container is read only');
    }

    public function __isset($name)
    {
        try {
            Core::di()->$name;

            return true;
        } catch (InvalidContainerItemException $exception) {
            return false;
        }
    }

    /**
     * @return array
     */
    public static function getRequestSignature(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function postRequestSignature(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function putRequestSignature(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function deleteRequestSignature(): array
    {
        return [];
    }
}
