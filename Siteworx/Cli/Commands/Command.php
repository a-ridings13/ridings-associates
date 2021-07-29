<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands;

use League\CLImate\CLImate;

/**
 * Class Command
 * @package Siteworx\Cli\Commands
 */
abstract class Command implements CommandInterface
{

    protected Climate $cli;

    public function __construct()
    {
        $this->cli = new CLImate();
    }
}
