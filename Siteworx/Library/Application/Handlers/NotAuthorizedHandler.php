<?php

declare(strict_types=1);

namespace Siteworx\Library\Application\Handlers;

use Siteworx\Library\Http\Exceptions\HttpException;
use Siteworx\Library\Http\Request;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};

final class NotAuthorizedHandler extends Handler
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
        $response = $exception->getResponse()->withStatus($exception->getStatusCode());

        if ($request->isXhr()) {
            return $response->withJson([
                'status' => 'error',
                'message' => $exception->getMessage() !== '' ?
                    $exception->getMessage() :
                    'You are not authorized to access this resource'
            ]);
        }

        return $response->write($this->container->view->render('Errors/401'));
    }
}
