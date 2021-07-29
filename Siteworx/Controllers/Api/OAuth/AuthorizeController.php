<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Api\OAuth;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\ConnectionResolver;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Siteworx\Controllers\Api\Controller;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\{Request, Response};
use Twig\Error\{LoaderError, RuntimeError, SyntaxError};
use Siteworx\Library\Crypt;
use Siteworx\Library\OAuth\Entities\{Client, User};

/**
 * Class AuthorizeController
 * @package Siteworx\Controllers\Api\OAuth
 */
final class AuthorizeController extends Controller
{

    /**
     * @param Request $request
     * @param Response|ResponseInterface $response
     * @param array $params
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function postAction(Request $request, Response $response, array $params = []): Response
    {
        /** @var User $user */
        $user = User::where('username', '=', $request->getParam('username', ''))
            ->orWhere('email', '=', $request->getParam('email', ''))
            ->get()
            ->first();

        if ($user === null || !Crypt::verifyPassword($request->getParam('password'), $user->password)) {
            Core::di()->log->warning(
                'User authentication failed: ' . $request->getParam('username') ?? $request->getParam('email', '')
            );

            return $response->write($this->view->render('OAuth/Login', [
                'failed' => '1',
                'auth' => []
            ]));
        }

        $authSerialized = $this->session->get('auth');

        /** @var AuthorizationRequest $auth */
        $auth = unserialize(
            $authSerialized,
            [
                'allowed_classes', [
                    AuthorizationRequest::class, ConnectionResolver::class, Client::class
                ]
            ]
        );

        $auth->setUser($user);
        $auth->setAuthorizationApproved(true);

        /** @var Response $response */
        $response = Core::di()->oauthserver->completeAuthorizationRequest($auth, $response);

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getAction(Request $request, Response $response, array $params = []): Response
    {
        if ($request->getParam('code') !== null) {
            $url = Core::di()->config->get('app_url') . '/api/oauth/access_token';
            $client = new Guzzle();

            try {
                $guzzleResponse = $client->post($url, [
                    'json' => [
                        'grant_type' => 'authorization_code',
                        'client_id' => env('CLIENT_ID', ''),
                        'client_secret' => env('CLIENT_SECRET', ''),
                        'code' => $request->getParam('code'),
                        'redirect_uri' => Core::di()->config->get('app_url') . '/api/oauth/authorize'
                    ]
                ]);

                return $response->write($this->view->render('OAuth/Login', [
                    'auth' => $guzzleResponse->getBody()->getContents()
                ]));
            } catch (ClientException $exception) {
                return $response->write('unable to trade code for token: ' . $exception->getResponse()->getBody());
            }
        }

        try {
            $auth = Core::di()->oauthserver->validateAuthorizationRequest($request);

            $this->session->set('auth', serialize($auth));

            return $response->write($this->view->render('OAuth/Login', [
                'auth' => []
            ]));
        } catch (OAuthServerException $e) {
            Core::di()->log->warning($e->getMessage() . ' ' . $e->getHint());

            return $response->write($e->getMessage() . ' ' . $e->getHint());
        }
    }
}
