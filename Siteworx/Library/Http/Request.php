<?php

declare(strict_types=1);

namespace Siteworx\Library\Http;

use Slim\Psr7\Request as SlimRequest;

/**
 * Class Request
 */
final class Request extends SlimRequest
{

    /**
     * @var array|null
     */
    private ?array $rawInput;

    /**
     * @param string $param
     * @param null $default
     * @return mixed|null
     */
    public function getServerParam(string $param, $default = null)
    {
        return $this->getServerParams()[$param] ?? $default;
    }

    /**
     * Is this an XHR request?
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return bool
     */
    public function isXhr(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->getHeader('content-type')[1] ?? '';
    }

    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $key     The parameter key.
     * @param  mixed  $default The default value.
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $params = $this->getParams();

        return $params[$key] ?? $default;
    }

    /**
     * Fetch associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param array|null $only list the keys to retrieve.
     *
     * @return array|null
     */
    public function getParams(array $only = null): ?array
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        $bodyParams = $this->jsonDecodeBody();

        if ($postParams) {
            $params = array_replace($params, (array) $postParams);
        }

        if ($bodyParams !== null) {
            $params = array_merge($bodyParams, $params);
        }

        if ($only) {
            $onlyParams = [];

            foreach ($only as $key) {
                if (array_key_exists($key, $params)) {
                    $onlyParams[$key] = $params[$key];
                }
            }

            return $onlyParams;
        }

        return $params;
    }

    /**
     * Use with caution input is not sanitized
     *
     * @return array|null
     */
    public function getRawParams(): ?array
    {
        return $this->rawInput;
    }

    /**
     * @param array $params
     * @return Request
     */
    public function withRaw(array $params): self
    {
        $clone = clone $this;
        $clone->rawInput = $params;

        return $clone;
    }

    /**
     * @return array|null
     */
    private function jsonDecodeBody(): ?array
    {
        $this->getBody()->rewind();
        $body = $this->getBody()->getContents();

        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return strtoupper($this->getMethod()) === 'POST';
    }

    /**
     * @return bool
     */
    public function isPut(): bool
    {
        return strtoupper($this->getMethod()) === 'PUT';
    }

    /**
     * @return bool
     */
    public function isGet(): bool
    {
        return strtoupper($this->getMethod()) === 'GET';
    }
}
