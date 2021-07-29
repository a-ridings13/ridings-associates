<?php

declare(strict_types=1);

namespace Siteworx\Controllers;

use Siteworx\Library\Http\{Request, Response};

/**
 * Interface ControllerInterface
 * @package Siteworx\Controllers
 */
interface ControllerInterface
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function getAction(Request $request, Response $response, array $params = []): Response;

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function postAction(Request $request, Response $response, array $params = []): Response;

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function putAction(Request $request, Response $response, array $params = []): Response;

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function deleteAction(Request $request, Response $response, array $params = []): Response;

    /**
     * @return array
     */
    public static function getRequestSignature(): array;

    /**
     * @return array
     */
    public static function postRequestSignature(): array;

    /**
     * @return array
     */
    public static function putRequestSignature(): array;

    /**
     * @return array
     */
    public static function deleteRequestSignature(): array;
}
