<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth;

use Siteworx\Library\OAuth\Entities\{AccessToken, Client, AccessTokenScope};
use League\OAuth2\Server\Entities\{AccessTokenEntityInterface, ClientEntityInterface, ScopeEntityInterface};
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

/**
 * Class AccessTokenRepository
 *
 * @package Siteworx\Library\OAuth
 */
final class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    /**
     * Create a new access token
     *
     * @param ClientEntityInterface|Client $clientEntity
     * @param ScopeEntityInterface[] $scopes
     * @param mixed $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): AccessTokenEntityInterface {
        $token = new AccessToken();
        $token->client_id = $clientEntity->id;
        $token->user_id = $userIdentifier;

        return $token;
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param AccessTokenEntityInterface | AccessToken $accessTokenEntity
     *
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $accessTokenEntity->save();

        //Iterate through scopes for token and save to db
        foreach ($accessTokenEntity->getScopes() as $scope) {
            $tokenScope = new AccessTokenScope();
            $tokenScope->token_id = $accessTokenEntity->id;
            $tokenScope->scope_id = $scope;
            $tokenScope->save();
        }
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId): void
    {
        /** @var AccessToken $token */
        $token = AccessToken::where('token', $tokenId)->get()->first();

        $token->is_revoked = true;
        $token->save();
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        /** @var AccessToken $token */
        $token = AccessToken::where('token', $tokenId)->get()->first();

        return $token === null || $token->is_revoked;
    }
}
