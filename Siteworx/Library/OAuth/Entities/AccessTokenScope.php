<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Siteworx\Library\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TokenScope
 *
 * @property int id
 * @property int token_id
 * @property int scope_id
 * @property Scope scope
 * @property AccessToken token
 *
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class AccessTokenScope extends Model
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
    public function token(): BelongsTo
    {
        return $this->belongsTo(AccessToken::class, 'id', 'token_id');
    }
}
