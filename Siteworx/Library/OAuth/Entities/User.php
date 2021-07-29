<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth\Entities;

use Carbon\Carbon;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Siteworx\Library\Models\Model;

/**
 * Class User
 *
 * @property int id
 * @property string username
 * @property string email
 * @property string password
 * @property Carbon last_login
 *
 * @package Siteworx\Library\OAuth\Entities
 */
final class User extends Model implements UserEntityInterface
{

    /**
     * @var array
     */
    protected $dates = [
        'last_login'
    ];

    protected $visible = [
        'id',
        'username',
        'email'
    ];

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->id;
    }
}
