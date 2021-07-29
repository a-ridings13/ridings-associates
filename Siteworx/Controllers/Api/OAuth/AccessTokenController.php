<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Api\OAuth;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Siteworx\Controllers\Api\Controller;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\{Request, Response, ResponseFactory};

final class AccessTokenController extends Controller
{

    /**
     * @param Request $request
     * @param Response|ResponseInterface|MessageInterface $response
     * @param array $params
     * @return Response
     */
    public function postAction(Request $request, Response $response, array $params = []): Response
    {
        try {
            $accessTokenResponse = Core::di()->oauthserver->respondToAccessTokenRequest($request, $response);
            $accessTokenResponse->getBody()->rewind();
            $body = $accessTokenResponse->getBody()->getContents();

            $response = ResponseFactory::factory();

            return $response->withHeader('Content-Type', 'application/json')->write($body);
        } catch (OAuthServerException $exception) {
            /** @var Response $response */
            $response = $exception->generateHttpResponse($response);
            $this->log->error($exception->getMessage());

            return $response;
        } catch (\Exception $exception) {
            return $response
                ->withStatus(500)
                ->write($exception->getMessage());
        }
    }
}
