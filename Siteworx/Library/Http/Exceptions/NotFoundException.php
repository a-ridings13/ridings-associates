<?php

declare(strict_types=1);

namespace Siteworx\Library\Http\Exceptions;

use Siteworx\Library\Http\{Request, Response, StatusCode};

class NotFoundException extends HttpException
{
    public function __construct(Request $request, Response $response, string $message = null)
    {
        parent::__construct($request, $response, $message ?? 'The requested resource could not be found.');
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return StatusCode::HTTP_NOT_FOUND;
    }
}
