<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\Queue;

use Siteworx\Library\Queue\Consumer;
use Siteworx\Cli\Commands\Command;

/**
 * Class StartConsumer
 * @package Siteworx\Cli\Commands\Queue
 */
final class StartConsumer extends Command
{

    /**
     * @return string
     */
    public static function getHelp(): string
    {
        return 'Starts the consumer process';
    }

    /**
     * @return int Return exit code
     * @throws \Exception
     */
    public function execute(): int
    {
        $consumer = new Consumer();
        $consumer->startConsumer();

        return 0;
    }

    /**
     * @return string
     */
    public static function commandSignature(): string
    {
        return 'start-consumer';
    }
}
