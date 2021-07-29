<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter\IdentityResolvers;

use Siteworx\Library\Http\Request;

interface IdentityResolverInterface
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public function getIdentity(Request $request): string;
}
