<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter\Exceptions;

class RateLimitExceededException extends \RuntimeException
{

    /**
     * @var string
     */
    protected string $key;

    public static function forKey(string $key): self
    {
        $exception = new static('Rate limit exceeded');

        $exception->key = $key;

        return $exception;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
