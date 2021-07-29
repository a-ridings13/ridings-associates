<?php

declare(strict_types=1);

namespace Siteworx\Library\Http\Exceptions;

use Siteworx\Library\Http\StatusCode;

class NotAuthorizedException extends HttpException
{

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return StatusCode::HTTP_UNAUTHORIZED;
    }
}
