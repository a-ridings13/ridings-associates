<?php

declare(strict_types=1);

namespace League\OAuth2\Server\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Siteworx\Library\Models\Model;
use Siteworx\Library\OAuth\Entities\AuthCode;
use Siteworx\Library\OAuth\Entities\Scope;

/**
 * Class AuthCodeScope
 *
 * @property int id
 * @property int auth_code_id
 * @property int scope_id
 *
 * @package League\OAuth2\Server\Entities
 */
final class AuthCodeScope extends Model
{
    /**
     * @return BelongsTo
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class, 'id', 'scope_id');
    }

    /**
     * @return BelongsTo
     */
    public function auth_code(): BelongsTo // @codingStandardsIgnoreLine
    {
        return $this->belongsTo(AuthCode::class, 'id', 'auth_code_id');
    }
}
