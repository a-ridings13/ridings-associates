<?php

declare(strict_types=1);

namespace Siteworx\Middleware;

use Siteworx\Library\Http\Request;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

final class SanitizationMiddleware extends Middleware
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
        if ($request->isPost() || $request->isPut()) {
            $params = $request->getParams();
            $request = $request->withRaw($params);

            if ($params !== null && is_array($params)) {
                $params = $this->deepFilter($params);
            }

            $request = $request->withParsedBody($params);
        }

        return $handler->handle($request);
    }

    /**
     * @param array $array
     * @return array
     */
    private function deepFilter(array $array): array
    {
        foreach ($array as $key => $item) {
            if (\is_array($item)) {
                $array[$key] = $this->deepFilter($item);

                continue;
            }

            if (is_float($array[$key])) {
                $array[$key] = (float) filter_var($item, FILTER_SANITIZE_STRING);
            } elseif (is_int($array[$key])) {
                $array[$key] = (int) filter_var($item, FILTER_SANITIZE_NUMBER_INT);
            } elseif (is_bool($array[$key])) {
                $array[$key] = (bool) filter_var($item, FILTER_VALIDATE_BOOLEAN);
            } else {
                $array[$key] = (string) filter_var($item, FILTER_SANITIZE_STRING);
            }
        }

        return $array;
    }
}
