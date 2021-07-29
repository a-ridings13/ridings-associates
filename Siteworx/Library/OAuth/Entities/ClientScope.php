<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Siteworx\Library\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ClientScope
 *
 * @property int id
 * @property int client_id
 * @property int scope_id
 * @property Scope scope
 * @property Client client
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class ClientScope extends Model
{

    /**
     * @return BelongsTo
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class, 'id', 'scope_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id', 'client_id');
    }
}
