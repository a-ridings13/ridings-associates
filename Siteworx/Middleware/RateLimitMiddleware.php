<?php

declare(strict_types=1);

namespace Siteworx\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Siteworx\Library\Http\{Request, Response, ResponseFactory, StatusCode};
use Siteworx\Library\RateLimiter\{Exceptions\RateLimitExceededException,
    IdentityResolvers\IdentityResolverInterface,
    IdentityResolvers\IpAddressIdentityResolver,
    MemcachedRateLimiter,
    Options,
    RateLimiterInterface};
use Siteworx\Library\Application\Core;

final class RateLimitMiddleware extends Middleware
{
    private const LIMIT_EXCEEDED_HTTP_STATUS_CODE = StatusCode::HTTP_TOO_MANY_REQUESTS;

    private const HEADER_LIMIT = 'X-RateLimit-Limit';
    private const HEADER_REMAINING = 'X-RateLimit-Remaining';
    private const HEADER_RESET = 'X-RateLimit-Reset';

    /**
     * @var RateLimiterInterface
     */
    private RateLimiterInterface $rateLimiter;

    /**
     * @var IdentityResolverInterface
     */
    private IdentityResolverInterface $identityResolver;

    /**
     * @var Options
     */
    private Options $options;

    /**
     * @var string
     */
    private string $identity;

    public function __construct(
        RateLimiterInterface $rateLimiter,
        IdentityResolverInterface $identityResolver,
        Options $options
    ) {
        $this->rateLimiter = $rateLimiter;
        $this->identityResolver = $identityResolver;
        $this->options = $options;
    }

    public static function createDefault(array $options = []): self
    {
        return new self(
            new MemcachedRateLimiter(Core::di()->cache, 120, 60),
            new IpAddressIdentityResolver(),
            Options::fromArray($options)
        );
    }

    private function isWhitelisted(Request $request): bool
    {
        $whitelist = $this->options->getWhitelist();

        return $whitelist($request);
    }

    private function resolveIdentity(Request $request): string
    {
        return $this->identityResolver->getIdentity($request);
    }

    private function onLimitExceeded(): Response
    {
        $response = ResponseFactory::factory(self::LIMIT_EXCEEDED_HTTP_STATUS_CODE);

        return $this->setRateLimitHeaders($response);
    }

    private function onBelowLimit(Request $request, RequestHandlerInterface $handler): Response
    {
        /** @var Response $response */
        $response = $handler->handle($request);

        return $this->setRateLimitHeaders($response);
    }

    private function setRateLimitHeaders(Response $response): Response
    {
        return $response
            ->withHeader(self::HEADER_LIMIT, (string) $this->rateLimiter->getLimit())
            ->withHeader(self::HEADER_REMAINING, (string) $this->rateLimiter->getRemainingAttempts($this->identity))
            ->withHeader(self::HEADER_RESET, (string) $this->rateLimiter->getResetAt($this->identity));
    }

    /**
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isWhitelisted($request)) {
            $handler->handle($request);
        }

        $this->identity = $this->resolveIdentity($request);

        try {
            $this->rateLimiter->hit($this->identity);

            return $this->onBelowLimit($request, $handler);
        } catch (RateLimitExceededException $ex) {
            return $this->onLimitExceeded();
        }
    }
}
