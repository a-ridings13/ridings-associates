<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Models\Model;
use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\OAuth2\Server\Entities\{AccessTokenEntityInterface,
    ClientEntityInterface,
    ScopeEntityInterface,
    Traits\AccessTokenTrait,
    Traits\EntityTrait,
    Traits\TokenEntityTrait};

/**
 * Class AccessToken
 *
 * @property int id
 * @property int client_id
 * @property int|null user_id
 * @property string token
 * @property boolean is_revoked
 * @property string expires
 * @property Client|ClientEntityInterface client
 *
 * @property bool is_user_token
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class AccessToken extends Model implements AccessTokenEntityInterface
{
    use TokenEntityTrait;
    use EntityTrait;
    use AccessTokenTrait;

    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return Token
     * @throws \Exception
     */
    public function convertToJWT(CryptKey $privateKey): Token
    {

        $key = new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase());

        $subject = $this->user_id ?? $this->client_id;

        $builder = (new Builder())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(time())
            ->issuedBy(Core::di()->config->get('app_url'))
            ->canOnlyBeUsedAfter(time())
            ->expiresAt($this->getExpiryDateTime()->getTimestamp())
            ->relatedTo($subject)
            ->withClaim('scopes', $this->getScopes());

        preg_match('/-----BEGIN CERTIFICATE-----[\S\n]*-----END CERTIFICATE-----/', $key->getContent(), $cert);

        /**
         * if a certificate is provided
         */
        if ($cert !== null) {
            $cert = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n"], '', $cert[0]);
            $builder->withHeader('x5c', $cert);
        }

        return $builder->getToken(new Sha256(), $key);
    }

    /**
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @param ScopeEntityInterface|Scope $scope
     */
    public function addScope(ScopeEntityInterface $scope): void
    {
        $this->scopes[$scope->getIdentifier()] = $scope->id;
    }


    /**
     * @param \DateTimeImmutable $dateTime
     */
    public function setExpiryDateTime(\DateTimeImmutable $dateTime): void
    {
        $this->expires = $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param ClientEntityInterface|Client $client
     */
    public function setClient(ClientEntityInterface $client): void
    {
        $this->client = $client;
        $this->client_id = $client->id;
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    public function getExpiryDateTime(): DateTime
    {
        return new \DateTime($this->expires);
    }

    /**
     * @return ClientEntityInterface
     */
    public function getClient(): ClientEntityInterface
    {
        if ($this->client === null) {
            $client = Client::find($this->client_id);
            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * @param $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->token = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->token;
    }

    public function setUserIdentifier($identifier): void
    {
        $this->user_id = $identifier;
    }

    public function getUserIdentifier(): ?int
    {
        return $this->user_id;
    }

    /**
     * @return bool
     */
    public function isIsUserToken(): bool
    {
        return $this->user_id !== null;
    }
}
