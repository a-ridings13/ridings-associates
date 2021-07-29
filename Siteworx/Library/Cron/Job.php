<?php

namespace Siteworx\Library\Cron;

use Carbon\Carbon;
use Cron\CronExpression;
use Monolog\Logger;
use Siteworx\Library\Application\Core;

/**
 * Class Job
 *
 * @package App\Library\Cron
 */
abstract class Job implements JobInterface
{

    /**
     * @var string
     */
    protected string $cronExpression = '* * * * *';

    private CronExpression $cron;

    /**
     * @var Logger
     */
    protected Logger $log;

    public function __construct()
    {
        $this->cron = CronExpression::factory($this->cronExpression);
        $this->log = Core::di()->logCron;
    }

    final public function checkAndRun(): void
    {
        if ($this->cron->isDue()) {
            $this->log->info('[' . static::class . ']' . ' Running');

            try {
                $this->run();
                $this->log
                    ->info('[' . static::class . ']' . ' Completed. Next run: ' . $this->nextRun()->toDateTimeString());
            } catch (CronException $e) {
                $this->log->error('[' . static::class . ']' . ' Failed! ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->log->error('[' . static::class . ']' . ' Failed! ' . $e->getMessage());
            }
        } else {
            $this->log->debug('[' . static::class . ']' . ' Not Scheduled... Skipping.');
        }
    }

    /**
     * @return Carbon
     */
    final public function nextRun(): Carbon
    {
        return Carbon::createFromTimestamp($this->cron->getNextRunDate()->getTimestamp());
    }

    /**
     * @return Carbon
     */
    final public function lastRun(): Carbon
    {
        return Carbon::createFromTimestamp($this->cron->getPreviousRunDate()->getTimestamp());
    }
}
