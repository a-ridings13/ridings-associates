<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Handlers;

use Siteworx\Library\Application\Container;
use Slim\Interfaces\ErrorHandlerInterface;

abstract class Handler implements ErrorHandlerInterface
{

    /**
     * @var Container
     */
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
