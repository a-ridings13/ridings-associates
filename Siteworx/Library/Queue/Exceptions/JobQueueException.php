<?php

namespace Siteworx\Library\Queue\Exceptions;

use Siteworx\Library\Queue\Jobs\JobInterface;

class JobQueueException extends \Exception
{

    /**
     * @var JobInterface
     */
    private JobInterface $job;

    public function __construct(JobInterface $job, string $message = '', int $code = 0, \Throwable $previous = null)
    {

        $this->job = $job;
        parent::__construct($message, $code, $previous);
    }

    public function getJobClass(): string
    {
        return \get_class($this->job);
    }
}
