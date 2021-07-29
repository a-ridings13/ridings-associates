<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Siteworx\Library\Models\Model;

/**
 * Class RefreshToken
 *
 * @property int id
 * @property string token
 * @property int access_token_id
 * @property bool is_revoked
 * @property Carbon expires_at
 * @property AccessToken access_token
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class RefreshToken extends Model implements RefreshTokenEntityInterface
{

    protected $casts = [
        'is_revoked' => 'boolean'
    ];

    protected $dates = [
        'expires_at'
    ];

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier): void
    {
        $this->token = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDateTime(): Carbon
    {
        return $this->expires_at;
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expires_at = $dateTime;
    }

    /**
     * @param AccessTokenEntityInterface|AccessToken $accessToken
     */
    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->access_token_id = $accessToken->id;
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(): AccessToken
    {
        return $this->access_token;
    }

    /**
     * @return BelongsTo
     */
    public function access_token(): BelongsTo // @codingStandardsIgnoreLine
    {
        return $this->belongsTo(AccessToken::class);
    }
}
