<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use Siteworx\Library\Models\Model;

/**
 * Class AuthCode
 *
 * @property int id
 * @property int user_id
 * @property int client_id
 * @property string redirect_uri
 * @property string code
 * @property bool is_revoked
 * @property Carbon expires_at
 * @property Client client
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class AuthCode extends Model implements AuthCodeEntityInterface
{
    use AuthCodeTrait;

    protected $dates = [
        'expires_at'
    ];

    protected $casts = [
        'is_revoked' => 'boolean'
    ];

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifier($identifier): void
    {
        $this->code = $identifier;
    }

    public function setRedirectUri($uri): void
    {
        $this->redirect_uri = $uri;
    }

    public function getRedirectUri(): string
    {
        return $this->redirect_uri;
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
     * @inheritDoc
     */
    public function setUserIdentifier($identifier): void
    {
        $this->user_id = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): int
    {
        return $this->user_id;
    }

    /**
     * @return bool
     */
    public function isIsRevoked(): bool
    {
        return $this->is_revoked;
    }

    /**
     * @inheritDoc
     */
    public function getClient()
    {
        return $this->client;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @param ClientEntityInterface|Client $client
     */
    public function setClient(ClientEntityInterface $client)
    {
        $this->client_id = $client->id;
    }

    /**
     * @inheritDoc
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        // TODO: Implement addScope() method.
    }

    /**
     * @inheritDoc
     */
    public function getScopes()
    {
        // TODO: Implement getScopes() method.
    }
}
