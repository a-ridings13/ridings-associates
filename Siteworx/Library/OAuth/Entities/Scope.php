<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Siteworx\Library\Models\Model;
use League\OAuth2\Server\Entities\{ScopeEntityInterface, Traits\EntityTrait};

/**
 * Class Scope
 *
 * @property int id
 * @property string scope_name
 * @property string scope_description
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class Scope extends Model implements ScopeEntityInterface
{
    use EntityTrait;

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->id;
    }
}
