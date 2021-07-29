<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter\IdentityResolvers;

use Psr\Http\Message\RequestInterface;

abstract class IdentityResolver implements IdentityResolverInterface
{
    protected static function getDefaultIdentity(RequestInterface $request): string
    {
        return sha1(implode('|', [
            $request->getMethod(),
            $request->getUri()->getPath(),
        ]));
    }
}
