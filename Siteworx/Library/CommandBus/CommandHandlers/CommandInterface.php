<?php

declare(strict_types=1);

namespace Siteworx\Library\CommandBus\CommandHandlers;

use Siteworx\Library\CommandBus\Commands\Command;

interface CommandInterface
{
    public function handle(Command $command);
}
