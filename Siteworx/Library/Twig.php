<?php

declare(strict_types=1);

namespace Siteworx\Library;

use Twig\Environment;
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

/**
 * Class Twig
 *
 * @package Siteworx\Library
 */
final class Twig extends Environment
{
    /**
     * example root page
     *
     * @param string $name
     * @param array $context
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render($name, array $context = array()): string
    {
        if (\strpos($name, '.twig') === false) {
            $name .= '.twig';
        }

        return parent::render($name, $context);
    }
}
