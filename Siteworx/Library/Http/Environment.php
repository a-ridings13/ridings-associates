<?php

declare(strict_types=1);

namespace Siteworx\Library\Http;

final class Environment extends \Slim\Psr7\Environment
{

    /**
     * @var array
     */
    private array $params;

    /**
     * Environment constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public static function factory(array $data = []): self
    {
        $array =  parent::mock($data);

        return new static($array);
    }

    public function all(): array
    {
        return $this->params;
    }

    public function get(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
}
