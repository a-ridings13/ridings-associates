<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Handlers;

use Carbon\Carbon;
use Siteworx\Library\Http\{Exceptions\HttpException, Request, ResponseFactory, StatusCode};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

final class NotFoundHandler extends Handler
{

    /**
     * @param ServerRequestInterface|Request $request
     * @param \Throwable|HttpException $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = $exception->response ??
            ResponseFactory::factory();

        if ($logErrors) {
            $this->container->log->warning('File not found: ' . $request->getUri());
        }

        if ($request->isXhr()) {
            return $response->withJson([
                'status' => 'error',
                'payload' => [
                    'message' => $exception->getMessage()
                ],
                'time' => Carbon::now()->timestamp
            ])
                ->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        return $response->write($this->container->view->render('Errors/404'))
            ->withStatus(StatusCode::HTTP_NOT_FOUND);
    }
}
