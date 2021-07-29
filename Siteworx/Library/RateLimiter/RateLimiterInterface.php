<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter;

use Siteworx\Library\RateLimiter\Exceptions\RateLimitExceededException;

interface RateLimiterInterface
{
    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @return int
     */
    public function getWindow(): int;

    /**
     * @param string $key
     *
     * @return void
     * @throws RateLimitExceededException
     *
     */
    public function hit(string $key): void;

    /**
     * @param string $key
     *
     * @return int
     */
    public function getRemainingAttempts(string $key): int;

    /**
     * @param string $key
     *
     * @return int Timestamp in the future
     */
    public function getResetAt(string $key): int;
}
