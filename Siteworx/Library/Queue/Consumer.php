<?php

declare(ticks=1);

namespace Siteworx\Library\Queue;

use Siteworx\Library\Application\Core;
use Siteworx\Library\Models\QueueLog;
use Carbon\Carbon;
use Siteworx\Library\Queue\Exceptions\NotAllowedException;
use Siteworx\Library\Queue\Jobs\Job;

/**
 * Class Consumer
 *
 * @package App\Library\Queue
 */
class Consumer
{

    /**
     * @var bool
     */
    private static bool $shut_down = false;

    /**
     * @var Messenger
     */
    private Messenger $messenger;

    /**
     * Consumer constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->messenger = new Messenger(Core::di()->config->get('aws.sqs'));
    }

    /**
     * register handlers
     */
    private function registerSignalHandlers(): void
    {
        Core::di()->logQueue->debug('Registering Shutdown Functions: ' . self::class);
        \pcntl_signal(SIGINT, [self::class, 'handleSignal']); // Interrupted (Ctrl-C is pressed)
        \pcntl_signal(SIGTERM, [self::class, 'handleSignal']);
        \pcntl_signal(SIGHUP, [self::class, 'handleSignal']);
    }

    /**
     * @param $signal
     */
    public static function handleSignal($signal): void
    {
        switch ($signal) {
            // Graceful
            case SIGINT:
            case SIGTERM:
            case SIGHUP:
                Core::di()->logQueue->info('Received stop signal... Letting all work complete before stopping');
                self::$shut_down = true;

                break;

            // Not Graceful
            case SIGKILL:
                exit(9);

                break;
        }
    }

    /**
     * @return int
     */
    public function startConsumer(): int
    {
        if (\function_exists('pcntl_signal')) {
            $this->registerSignalHandlers();
            pcntl_signal_dispatch();
        } else {
            Core::di()->logQueue->warning('pcntl unavailable.  Consumer will not listen for shutdown requests!');
        }

        $sleep = Core::di()->config->get('dev_mode', false) ? 1 : 15;

        while (true) {
            $class = null;
            $queue = null;
            $message = null;

            if (self::$shut_down) {
                return 0;
            }

            $message = $this->messenger->getMessage();

            if ($message instanceof Message) {
                Core::di()->logQueue->info('Received Job:' . $message->getId());
                Core::di()->logQueue->debug('Received Payload:' . $message->toJson());

                /** @var QueueLog $queue */
                $queue = QueueLog::where('message_id', $message->getId())->get()->first();

                if (!$queue instanceof QueueLog) {
                    Core::di()->logQueue->warning('Unable to fine job: ' . $message->getId());

                    continue;
                }

                /** @var Job $class */
                $class = new $queue->job();

                $this->performTask(
                    $class,
                    $queue,
                    json_decode($queue->message_content, true, 512, JSON_THROW_ON_ERROR)
                );

                Core::di()->logQueue->info('Completed Job:' . $message->getId());
            }

            sleep($sleep);
        }

        return 0;
    }

    /**
     * @param Job $class
     * @param array $params
     * @param QueueLog $queue
     */
    private function performTask(Job $class, QueueLog $queue, array $params = []): void
    {

        Core::di()->logQueue->info('Starting Job ' . class_basename($class));

        $queue->status = QueueLog::STATUS_RUNNING;
        $queue->started_at = Carbon::now()->toDateTimeString();
        $queue->save();

        try {
            $class->assertAllowed();
            $class->runJob($params);
            $queue->status = QueueLog::STATUS_COMPLETE;
            $queue->completed_at = Carbon::now()->toDateTimeString();
            $queue->save();
            $class->onSuccess();
            Core::di()->logQueue->info('Completed Job ' . class_basename($class));
        } catch (NotAllowedException $exception) {
            Core::di()->logQueue->warning($exception->getMessage());
            $queue->status = QueueLog::STATUS_NOT_ALLOWED;
            $queue->save();
            Core::di()->logQueue->warning('Job Failed: ' . class_basename($class) . ' Reason: Not Allowed');
        } catch (\Throwable $exception) {
            Core::di()->logQueue->warning($exception->getMessage());
            $queue->status = QueueLog::STATUS_FAILED;
            $queue->save();
            $class->onFail();
            Core::di()
                ->logQueue
                ->error('Job Failed: ' . class_basename($class) . ' Reason: ' . $exception->getMessage());
        }
    }
}
