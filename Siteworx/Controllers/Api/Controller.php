<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Api;

use Carbon\Carbon;
use Siteworx\Controllers\Controller as BaseController;
use Siteworx\Library\Http\{Response, StatusCode};
use Siteworx\Library\OAuth\Entities\AccessToken;

/**
 * Class Controller
 *
 * @property AccessToken accessToken
 *
 * @package Siteworx\Controllers\Api
 */
abstract class Controller extends BaseController
{

    /**
     * @var array
     */
    private array $payload = [];

    /**
     * @var bool
     */
    private bool $withPagination = false;

    /**
     * @var int
     */
    private int $page = 1;

    /**
     * @var int
     */
    private int $total = 0;

    /**
     * @var int
     */
    private int $statusCode = StatusCode::HTTP_OK;

    protected function setError(
        string $message,
        int $statusCode = StatusCode::HTTP_BAD_REQUEST,
        array $fields = []
    ): self {
        $this->payload['error'] = $message;
        $this->payload['fields'] = $fields;
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param array $payload
     * @param int $statusCode
     * @return Controller
     */
    protected function setPayload(array $payload, int $statusCode = StatusCode::HTTP_OK): self
    {
        $this->payload = $payload;
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param bool $withPagination
     * @return Controller
     */
    public function setWithPagination(bool $withPagination): self
    {
        $this->withPagination = $withPagination;

        return $this;
    }

    /**
     * @param int $page
     * @return Controller
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $total
     * @return Controller
     */
    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @param Response $response
     * @return Response
     */
    protected function formatResponse(Response $response): Response
    {
        $responsePayload = [
            'status' => $this->statusCode === StatusCode::HTTP_OK ? 'ok' : 'error',
            'md5' => hash('md5', json_encode($this->payload, JSON_THROW_ON_ERROR, 512)),
            'sha1' => hash('sha1', json_encode($this->payload, JSON_THROW_ON_ERROR, 512)),
            'time' => Carbon::now()->timestamp,
            'payload' => $this->payload
        ];

        if ($this->withPagination) {
            $responsePayload['pagination'] = [
                'page' => $this->page,
                'totalItems' => $this->total
            ];
        }

        return $response->withStatus($this->statusCode)->withJson($responsePayload);
    }
}
