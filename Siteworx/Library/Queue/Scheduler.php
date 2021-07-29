<?php

namespace Siteworx\Library\Queue;

use Monolog\Logger;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Models\QueueLog;

/**
 * Class Scheduler
 *
 * @package App\Library\Queue
 */
class Scheduler
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * @param string $jobName
     * @param array  $params
     * @param int    $delay
     *
     * @return QueueLog $queue;
     * @throws \Exception
     */
    public static function scheduleJob(string $jobName, array $params = [], int $delay = 0): QueueLog
    {
        $log = Core::di()->logQueue;

        $messenger = new Messenger(Core::di()->config->get('aws.sqs'));

        $messageId = $messenger->sendMessage(\GuzzleHttp\json_encode($params), $delay);

        $log->info('Sent Message: ' . $messageId);

        $queue = new QueueLog();

        $queue->job = $jobName;
        $queue->message_id = $messageId;
        $queue->message_content = json_encode($params, JSON_THROW_ON_ERROR, 512);
        $queue->status = QueueLog::STATUS_WAITING;
        $queue->save();

        return $queue;
    }
}
