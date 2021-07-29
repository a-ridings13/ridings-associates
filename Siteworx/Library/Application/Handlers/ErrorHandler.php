<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Handlers;

use Siteworx\Library\Http\{Exceptions\HttpException, Request, ResponseFactory};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};
use Whoops\{Handler\JsonResponseHandler, Handler\PrettyPageHandler, Run};

final class ErrorHandler extends Handler
{
    /**
     * @param ServerRequestInterface|Request $request
     * @param \Throwable $exception
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
        $statusCode = 500;
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $description = $exception->getMessage();
            $response =  $exception->getResponse();
        } else {
            $response = ResponseFactory::factory($statusCode);
        }

        if ($logErrors) {
            $this->container->log->critical($exception->getMessage());
        }

        if ($logErrorDetails) {
            $this->container->log->critical((string) $exception);
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof \Exception || $exception instanceof \Throwable)
            && $displayErrorDetails
        ) {
            $whoops = new Run();
            $handler = $request->isXhr() ? new JsonResponseHandler() : new PrettyPageHandler();
            $whoops->appendHandler($handler);
            $whoops->handleException($exception);
            exit;
        }

        if ($request->isXhr()) {
            $error = [
                'statusCode' => $statusCode,
                'error' => [
                    'message' => $description,
                ],
            ];

            return $response->withJson($error);
        }

        return $response->write($this->container->view->render('Errors/500'));
    }
}
