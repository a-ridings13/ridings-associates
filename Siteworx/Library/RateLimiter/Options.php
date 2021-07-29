<?php

declare(strict_types=1);

namespace Siteworx\Library\RateLimiter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Options
{

    /**
     * @var callable
     */
    protected $whitelist;

    /**
     * @var callable
     */
    protected $limitExceededHandler;

    public function __construct(callable $whitelist, callable $limitExceededHandler)
    {
        $this->whitelist = $whitelist;
        $this->limitExceededHandler = $limitExceededHandler;
    }

    public static function fromArray(array $options): self
    {
        $options = array_merge(self::getDefaultOptions(), $options);

        return new self(
            $options['whitelist'],
            $options['limitExceededHandler']
        );
    }

    public function getWhitelist(): callable
    {
        return $this->whitelist;
    }

    public function getLimitExceededHandler(): callable
    {
        return $this->limitExceededHandler;
    }

    private static function getDefaultOptions(): array
    {
        return [
            'whitelist' => function (RequestInterface $request) {
                return false;
            },
            'limitExceededHandler' => function (RequestInterface $request, ResponseInterface $response) {
                return $response;
            },
        ];
    }
}
