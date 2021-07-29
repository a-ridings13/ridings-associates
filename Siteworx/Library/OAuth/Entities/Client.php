<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Siteworx\Library\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use League\OAuth2\Server\Entities\{ClientEntityInterface, Traits\ClientTrait, Traits\EntityTrait};

/**
 * Class Client
 *
 * @property int                    id
 * @property string                 client_id
 * @property string                 client_secret
 * @property string                 client_name
 * @property bool                   confidential
 * @property string                 rand_string
 * @property string                 grant_type
 * @property Collection             domains
 *
 * @property Collection             scopes
 * @package Siteworx\Library\OAuth\Entities
 */
final class Client extends Model implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    protected $visible = [
        'id',
        'client_id',
        'client_secret',
        'client_name',
        'grant_type'
    ];

    protected $casts = [
        'confidential' => 'boolean'
    ];

    public const CLIENT_CREDENTIALS = 'client_credentials';
    public const AUTHORIZATION_CODE = 'authorization_code';

    public function scopes(): HasManyThrough
    {
        return $this->hasManyThrough(
            Scope::class,
            ClientScope::class,
            'client_id',
            'id',
            'id',
            'scope_id'
        );
    }

    public static function grants(): array
    {
        return [
            self::CLIENT_CREDENTIALS,
            self::AUTHORIZATION_CODE
        ];
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->client_name;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    public function getRedirectUri()
    {
        $domains = $this->domains;

        return $domains->isNotEmpty() ? array_column($domains->toArray(), 'domain') : [];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(ClientRedirectDomain::class);
    }
}
