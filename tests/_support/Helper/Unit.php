<?php

namespace Helper;

use Codeception\Module;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\{Environment, Request, RequestFactory, Response, StatusCode};

/**
 * Class Unit
 * @package Helper
 */
class Unit extends Module
{

    /**
     * @var Core
     */
    private $app;

    /**
     * Define custom actions here
     * @param array $options
     * @return Request|RequestInterface
     */
    public function getMockRequest(array $options = []): Request
    {
        return RequestFactory::createFromEnvironment(Environment::factory($options));
    }

    /**
     * @param Request $request
     * @param int $expectedStatus
     * @return Response|ResponseInterface
     */
    public function processRequest(Request $request, int $expectedStatus = StatusCode::HTTP_OK): Response
    {
        $this->app = $this->createApplication();
        $response = $this->app->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $response->getBody()->rewind();

        return $response;
    }
    /**
     * @return Core
     */
    public function createApplication(): Core
    {
        global $app;
        $app = Core::factory();

        return $app;
    }
}
