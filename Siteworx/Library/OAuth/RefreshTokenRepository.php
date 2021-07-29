<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Siteworx\Library\OAuth\Entities\RefreshToken;

/**
 * Class RefreshTokenRepository
 * @package Siteworx\Library\OAuth
 */
final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function getNewRefreshToken(): RefreshToken
    {
        return new RefreshToken();
    }

    /**
     * @param RefreshTokenEntityInterface|RefreshToken $refreshTokenEntity
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $refreshTokenEntity->save();
    }

    /**
     * @inheritDoc
     */
    public function revokeRefreshToken($tokenId): void
    {
        $token = RefreshToken::where('token', '=', $tokenId)->get()->first();

        if ($token instanceof RefreshToken) {
            $token->is_revoked = true;
            $token->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $token = RefreshToken::where('token', '=', $tokenId)->get()->first();

        if ($token instanceof RefreshToken) {
            return $token->is_revoked;
        }

        return true;
    }
}
