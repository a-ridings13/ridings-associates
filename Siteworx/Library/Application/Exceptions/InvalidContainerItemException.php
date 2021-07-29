<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class InvalidContainerItemException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
    /**
     * @param string $id The unknown identifier
     */
    public function __construct($id)
    {
        parent::__construct(\sprintf('Identifier "%s" is not defined.', $id));
    }
}
