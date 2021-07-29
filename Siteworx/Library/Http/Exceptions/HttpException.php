<?php

declare(strict_types=1);

namespace Siteworx\Library\Http\Exceptions;

use Siteworx\Library\Http\{Request, Response};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

abstract class HttpException extends \Exception implements ErrorHandlerInterface
{

    /**
     * @var Request
     */
    public Request $request;

    /**
     * @var Response
     */
    public ResponseInterface $response;

    public function __construct(
        Request $request,
        Response $response,
        string $message = null,
        $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? '', $code, $previous);
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return int
     */
    abstract public function getStatusCode(): int;

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        return $this->response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
