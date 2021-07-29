<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Siteworx\Library\Models\Model;

/**
 * Class ClientRedirectDomain
 *
 * @property int id
 * @property int client_id
 * @property string domain
 * @property Client client
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class ClientRedirectDomain extends Model
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
