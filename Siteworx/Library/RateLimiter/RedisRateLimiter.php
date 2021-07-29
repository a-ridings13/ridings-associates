<?php

declare(strict_types=1);

namespace RateLimit;

use Siteworx\Library\RateLimiter\RateLimiter;

final class RedisRateLimiter extends RateLimiter
{

    /**
     * @var \Redis
     */
    private \Redis $redis;

    public function __construct(\Redis $redis, int $limit, int $window)
    {
        $this->redis = $redis;

        parent::__construct($limit, $window);
    }

    protected function get(string $key, int $default): int
    {
        $value = $this->redis->get($key);

        if ($value === false) {
            return $default;
        }

        return (int) $value;
    }

    protected function init(string $key): void
    {
        $this->redis->setex($key, $this->window, 1);
    }

    protected function increment(string $key): void
    {
        $this->redis->incr($key);
    }

    protected function ttl(string $key): int
    {
        $ttl = $this->redis->pttl($key);

        if ($ttl === -1) {
            $this->redis->expire($key, $this->window);
            $ttl = $this->window * 1000;
        }

        return max((int) ceil($ttl / 1000), 0);
    }
}
