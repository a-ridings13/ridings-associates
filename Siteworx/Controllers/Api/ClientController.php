<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Api;

use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\Request;
use Siteworx\Library\Http\Response;

/**
 * Class ClientController
 * @package Siteworx\Controllers\Api
 */
final class ClientController extends Controller
{

    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function getAction(Request $request, Response $response, array $params = []): Response
    {
        if ($this->accessToken->isIsUserToken()) {
            return $this->setPayload(Core::di()->user->toArray())->formatResponse($response);
        }

        return $this->setPayload(Core::di()->client->toArray())->formatResponse($response);
    }
}
