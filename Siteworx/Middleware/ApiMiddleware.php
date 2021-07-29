<?php

declare(strict_types=1);

namespace Siteworx\Middleware;

use Siteworx\Library\Http\Request;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ApiMiddleware
 * @package Siteworx\Middleware
 */
final class ApiMiddleware extends Middleware
{

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withHeader('X-Requested-With', 'XMLHttpRequest');

        return $handler->handle($request);
    }
}
