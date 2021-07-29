<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands;

/**
 * Interface CommandInterface
 * @package Siteworx\Cli\Commands
 */
interface CommandInterface
{
    /**
     * @return string
     */
    public static function getHelp(): string;

    /**
     * @return int Return exit code
     */
    public function execute(): int;

    /**
     * @return string
     */
    public static function commandSignature(): string;
}
