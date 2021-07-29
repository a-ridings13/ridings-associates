<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Siteworx\Library\OAuth\Entities\AuthCode;

/**
 * Class AuthCodeRepository
 * @package Siteworx\Library\OAuth
 */
final class AuthCodeRepository implements AuthCodeRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function getNewAuthCode(): AuthCode
    {
        return new AuthCode();
    }

    /**
     * @param AuthCodeEntityInterface|AuthCode $authCodeEntity
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $authCodeEntity->save();
    }

    /**
     * @inheritDoc
     */
    public function revokeAuthCode($codeId): void
    {
        $authCode = AuthCode::where('code', '=', $codeId)->get()->first();

        if ($authCode instanceof AuthCode) {
            $authCode->is_revoked = true;
            $authCode->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = AuthCode::where('code', '=', $codeId)->get()->first();

        if ($authCode instanceof AuthCode) {
            return $authCode->is_revoked;
        }

        return true;
    }
}
