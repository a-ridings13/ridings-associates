<?php

declare(strict_types=1);

namespace Siteworx\Library\Queue\Jobs;

use Siteworx\Library\Queue\Exceptions\NotAllowedException;
use Siteworx\Library\Application\Core;

abstract class Job implements JobInterface
{
    /**
     * @return bool
     * @throws NotAllowedException
     */
    public function assertAllowed(): bool
    {
        /** Prod jobs are always allowed */
        if (Core::di()->config->get('dev_mode', false) === false) {
            return true;
        }

        $class = static::class;

        if (!\in_array($class, Core::di()->config->get('whitelisted_jobs', []), false)) {
            throw new NotAllowedException('Job now whitelisted');
        }

        return true;
    }

    public function onSuccess(): void
    {
        //
    }

    public function onFail(): void
    {
        //
    }
}
