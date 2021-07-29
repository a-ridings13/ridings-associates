<?php

namespace Siteworx\Library\Models;

/**
 * Class Session
 *
 * @property string key
 * @property string ip
 * @property string user_agent
 * @property string session
 * @property bool   remember
 *
 * @package Siteworx\Models
 */
class Session extends Model
{

    public $incrementing = false;

    protected $casts = [
        'remember' => 'boolean'
    ];

    protected $primaryKey = 'key';


    public function getSessionAttribute($value): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function setSessionAttribute($value): void
    {
        $this->attributes['session'] = json_encode($value, JSON_THROW_ON_ERROR, 512);
    }
}
