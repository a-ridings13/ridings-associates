<?php

declare(strict_types=1);

namespace Siteworx\Library\Email\Transports;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Siteworx\Mail\Transports\TransportInterface;

/**
 * Class LogTransport
 * @package Siteworx\Library\Email\Transports
 */
final class LogTransport implements TransportInterface
{

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    public function setCache(CacheInterface $cache): void
    {
        //
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function sentMailPayload(array $payload): void
    {
        $this->logger->info(json_encode($payload, JSON_THROW_ON_ERROR, 512));
    }
}
