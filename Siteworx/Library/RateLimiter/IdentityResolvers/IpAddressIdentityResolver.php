<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter\IdentityResolvers;

use Siteworx\Library\Http\Request;

final class IpAddressIdentityResolver extends IdentityResolver
{
    /**
     * {@inheritdoc}
     */
    public function getIdentity(Request $request): string
    {
        if (!$request instanceof Request) {
            return self::getDefaultIdentity($request);
        }

        $serverParams = $request->getServerParams();

        if (array_key_exists('HTTP_CLIENT_IP', $serverParams)) {
            return $serverParams['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            return $serverParams['HTTP_X_FORWARDED_FOR'];
        }

        return $serverParams['REMOTE_ADDR'] ?? self::getDefaultIdentity($request);
    }
}
