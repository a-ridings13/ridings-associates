<?php

namespace Siteworx\Library\Models;

use Siteworx\Library\Queue\Jobs\JobInterface;

/**
 * Class QueueLog
 *
 * @property int                 id
 * @property JobInterface|string job
 * @property string              message_id
 * @property string              message_content
 * @property string              started_at
 * @property string              completed_at
 * @property int                 status
 *
 * @package Siteworx\Models
 */
class QueueLog extends Model
{
    public const STATUS_WAITING = 1;
    public const STATUS_RUNNING = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_COMPLETE = 4;
    public const STATUS_DIFFERED = 5;
    public const STATUS_NOT_ALLOWED = 6;

    public static function status_html(int $status): string // @codingStandardsIgnoreLine
    {
        $color = '';
        $text = '';

        switch ($status) {
            case self::STATUS_WAITING:
                $color = 'info';
                $text = 'Waiting';

                break;
            case self::STATUS_RUNNING:
                $color = 'primary';
                $text = 'Running';

                break;
            case self::STATUS_FAILED:
                $color = 'danger';
                $text = 'Failed';

                break;
            case self::STATUS_COMPLETE:
                $color = 'success';
                $text = 'Complete';

                break;
            case self::STATUS_NOT_ALLOWED:
                $color = 'warning';
                $text = 'Not Allowed';

                break;
        }

        return sprintf('<span class="label label-%s">%s</span>', $color, $text);
    }
}
