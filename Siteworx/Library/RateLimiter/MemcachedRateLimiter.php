<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter;

use Carbon\Carbon;

/**
 * Class MemcachedRateLimiter
 * @package Siteworx\Library\RateLimiter
 */
final class MemcachedRateLimiter extends RateLimiter
{

    private \Memcached $memcached;

    public function __construct(\Memcached $memcached, int $limit, int $window)
    {
        $this->memcached = $memcached;

        parent::__construct($limit, $window);
    }

    protected function get(string $key, int $default): int
    {
        return $this->memcached->get('rl.' . $key) !== false ? $this->memcached->get('rl.' . $key) : $default;
    }

    protected function init(string $key): void
    {
        $this->memcached->set('rl.' . $key, 1, $this->window);
        $this->memcached->set('rl.' . $key . '.ttl', Carbon::now()->timestamp, $this->window);
    }

    protected function increment(string $key): void
    {
        $iteration = $this->get($key, 1);
        $timeLeft = $this->timeLeft($key);
        $this->memcached->set('rl.' . $key, ++$iteration, $timeLeft);
    }

    private function ttlTime(string $key)
    {
        $time = $this->memcached->get('rl.' . $key . '.ttl');

        if ($time === false) {
            $this->memcached->set('rl.' . $key . '.ttl', Carbon::now()->timestamp, $this->window);
        }

        return $this->memcached->get('rl.' . $key . '.ttl');
    }

    protected function ttl(string $key): int
    {
        return $this->timeLeft($key);
    }

    /**
     * @param string $key
     * @return int|mixed
     */
    protected function timeLeft(string $key)
    {
        $time = $this->ttlTime($key);
        $now = Carbon::now()->timestamp;
        $diff = $now - $time;

        return $this->window - $diff;
    }
}
