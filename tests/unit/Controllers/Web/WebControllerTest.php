<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers\Web;

use Codeception\Test\Unit;
use Siteworx\Library\Http\Response;

abstract class WebControllerTest extends Unit
{

    /** @var \UnitTester */
    protected $tester;

    /**
     * @var string
     */
    protected $uri;

    public function getActionTest(): void
    {
        $request = $this->tester->getMockRequest([
            'REQUEST_URI' => $this->uri
        ]);

        /** @var Response $response */
        $response = $this->tester->processRequest($request);
        $body = $response->getBody()->getContents();
        $this->tester->assertNotEmpty($body);
    }
}
