<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter;

final class InMemoryRateLimiter extends RateLimiter
{

    /**
     * @var array
     */
    protected array $store = [];

    protected function get(string $key, int $default): int
    {
        if (
            !$this->has($key)
            || $this->hasExpired($key)
        ) {
            return $default;
        }

        return $this->store[$key]['current'];
    }

    private function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    private function hasExpired(string $key): bool
    {
        return time() > $this->store[$key]['expires'];
    }

    protected function init(string $key): void
    {
        $this->store[$key] = [
            'current' => 1,
            'expires' => time() + $this->window,
        ];
    }

    protected function increment(string $key): void
    {
        $this->store[$key]['current']++;
    }

    protected function ttl(string $key): int
    {
        if (!isset($this->store[$key])) {
            return 0;
        }

        return max($this->store[$key]['expires'] - time(), 0);
    }
}
