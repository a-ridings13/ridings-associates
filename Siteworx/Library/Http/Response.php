<?php

declare(strict_types=1);

namespace Siteworx\Library\Http;

use Psr\Http\Message\UriInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * Class Response
 *
 * @method self withStatus($code, $reasonPhrase = '')
 * @method self withHeader($name, $value)
 *
 * @package Siteworx\Library\Http
 */
final class Response extends SlimResponse
{
    /**
     * @param $contents
     * @return Response
     */
    public function write($contents): self
    {
        $clone = clone $this;
        $clone->getBody()->write($contents);

        return $clone;
    }

    /**
     * Json.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param  mixed $data   The data
     * @param  int   $status The HTTP status code.
     * @param  int   $encodingOptions Json encoding options
     *
     * @return static
     *
     * @throws \RuntimeException
     */
    public function withJson($data, $status = null, $encodingOptions = 0): Response
    {
        $response = $this->write(json_encode($data, JSON_THROW_ON_ERROR | $encodingOptions, 512));

        /** @var Response $responseWithJson */
        $responseWithJson = $response->withHeader('Content-Type', 'application/json');

        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }

        return $responseWithJson;
    }

    /**
     * Redirect.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param  string|UriInterface $url    The redirect destination.
     * @param  int|null            $status The redirect HTTP status code.
     *
     * @return static
     */
    public function withRedirect($url, $status = null): Response
    {
        $responseWithRedirect = $this->withHeader('Location', (string) $url);

        if ($status === null && $this->getStatusCode() === StatusCode::HTTP_OK) {
            $status = StatusCode::HTTP_FOUND;
        }

        if ($status !== null) {
            return $responseWithRedirect->withStatus($status);
        }

        return $responseWithRedirect;
    }
}
