<?php

declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Test\Unit;
use Monolog\Logger;
use Noodlehaus\Config;
use Siteworx\Library\Application\Container;
use Siteworx\Library\Application\Exceptions\InvalidContainerItemException;
use Siteworx\Library\Twig;

class ContainerTest extends Unit
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Container
     */
    protected $container;

    private const DI_CLASSES = [
        'config' => Config::class,
        'view' => Twig::class,
        'log' => Logger::class
    ];

    protected function _before()
    {
        $this->container = new Container();
    }

    public function testContainerReturnsFalse(): void
    {
        $this->tester->assertFalse($this->container->has('NotInDI'));
    }

    public function testThrowsException(): void
    {
        $this->tester->expectException(InvalidContainerItemException::class, function () {
            $this->container->notInDi;
        });
    }

    public function testCanSetNewDi(): void
    {
        $this->container->newDi = static function () {
            return true;
        };

        $this->assertTrue($this->container->newDi);
    }

    public function testDiClasses(): void
    {
        foreach (self::DI_CLASSES as $CLASS => $DI_CLASS) {
            $class = $this->container->$CLASS;
            $this->assertInstanceOf($DI_CLASS, $class);
        }
    }
}