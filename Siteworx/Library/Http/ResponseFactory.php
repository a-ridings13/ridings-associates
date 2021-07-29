<?php

declare(strict_types=1);

namespace Siteworx\Library\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactory implements ResponseFactoryInterface
{

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return Response
     */
    public static function factory(int $code = 200, string $reasonPhrase = ''): Response
    {
        $response = new static();

        return $response->createResponse($code, $reasonPhrase);
    }

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code; defaults to 200
     * @param string $reasonPhrase Reason phrase to associate with status code
     *     in generated response; if none is provided implementations MAY use
     *     the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface|Response
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code);
    }
}
