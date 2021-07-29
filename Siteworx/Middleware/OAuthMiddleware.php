<?php

declare(strict_types=1);

namespace Siteworx\Middleware;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\ResponseFactory;
use Siteworx\Library\OAuth\Entities\{AccessToken, Client, User};

/**
 * Class OAuthMiddleware
 * @package Siteworx\Middleware
 */
final class OAuthMiddleware extends Middleware
{

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server = Core::di()->resourceserver;

        try {
            $request = $server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse(ResponseFactory::factory());
        } catch (\Exception $exception) {
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse(ResponseFactory::factory());
        }

        Core::di()['client'] = static function () use ($request) {
            $headers = $request->getAttributes();

            return Client::where('client_id', $headers['oauth_client_id'])->get()->first();
        };

        Core::di()['user'] = static function () use ($request) {
            $headers = $request->getAttributes();

            return User::find($headers['oauth_user_id'] ?? 0);
        };

        Core::di()['accessToken'] = static function () use ($request) {
            $headers = $request->getAttributes();

            return AccessToken::where('token', '=', $headers['oauth_access_token_id'])->get()->first();
        };

        return $handler->handle($request);
    }
}
