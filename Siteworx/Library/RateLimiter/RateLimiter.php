<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter;

use Siteworx\Library\RateLimiter\Exceptions\RateLimitExceededException;

abstract class RateLimiter implements RateLimiterInterface
{

    /**
     * @var int
     */
    protected int $limit;

    /**
     * @var int
     */
    protected int $window;

    /**
     * @var string
     */
    protected string $key;

    public function __construct(int $limit, int $window)
    {
        $this->limit = $limit;
        $this->window = $window;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getWindow(): int
    {
        return $this->window;
    }

    /**
     * {@inheritdoc}
     */
    public function hit(string $key): void
    {
        $current = $this->getCurrent($key);

        if ($current >= $this->limit) {
            throw RateLimitExceededException::forKey($key);
        }

        if ($current === 0) {
            $this->init($key);

            return;
        }

        $this->increment($key);
    }

    protected function getCurrent(string $key): int
    {
        return $this->get($key, 0);
    }

    abstract protected function get(string $key, int $default): int;

    abstract protected function init(string $key);

    abstract protected function increment(string $key);

    /**
     * {@inheritdoc}
     */
    public function getRemainingAttempts(string $key): int
    {
        return max(0, $this->limit - $this->getCurrent($key));
    }

    /**
     * {@inheritdoc}
     */
    public function getResetAt(string $key): int
    {
        return time() + $this->ttl($key);
    }

    abstract protected function ttl(string $key): int;
}
