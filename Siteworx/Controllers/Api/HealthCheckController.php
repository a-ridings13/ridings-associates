<?php

declare(strict_types=1);

namespace Siteworx\Controllers\Api;

use Siteworx\Library\Http\{Request, Response, StatusCode};

/**
 * Class HealthCheckController
 *
 * @package Siteworx\Controllers\Api
 */
final class HealthCheckController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return Response
     */
    public function getAction(Request $request, Response $response, array $params = []): Response
    {
        try {
            /** Assert Config */
            $this->config->all();

            /** Assert render views */
            $this->view->render('Index');
        } catch (\Exception $exception) {
            return $this->setPayload(['status' => 'error'], StatusCode::HTTP_INTERNAL_SERVER_ERROR)
                ->formatResponse($response);
        }

        return $this->setPayload(['status' => 'ok'])->formatResponse($response);
    }
}
