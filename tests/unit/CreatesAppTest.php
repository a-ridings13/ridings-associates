<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Siteworx\Library\Application\Container;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Http\Request;
use UnitTester;

class CreatesAppTest extends Unit
{

    /**
     * @var UnitTester
     */
    protected $tester;

    // tests
    public function testAppIsCreated(): void
    {
        $request = $this->tester->getMockRequest();
        $this->tester->assertInstanceOf(Request::class, $request);
        $app = $this->tester->createApplication();
        $this->tester->assertInstanceOf(Core::class, $app);
    }

    public function testReturnsContainer(): void
    {
        /** @var Core $app */
        $app = $this->tester->createApplication();
        $container = $app->getContainer();

        $this->assertInstanceOf(Container::class, $container);

        $container = Core::di();
        $this->assertInstanceOf(Container::class, $container);
    }
}