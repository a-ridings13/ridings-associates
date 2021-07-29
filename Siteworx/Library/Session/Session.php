<?php

declare(strict_types=1);

namespace Siteworx\Library\Session;

use Siteworx\Library\Application\Core;
use Siteworx\Library\Cookie;
use Siteworx\Library\Session\Drivers\SessionDriverInterface;
use Siteworx\Library\Utilities\Helpers;

/**
 * Class Session
 *
 * @package App\Library
 */
final class Session
{

    /**
     * @var SessionDriverInterface
     */
    private SessionDriverInterface $driver;

    /**
     * @var string
     */
    private ?string $key;

    /**
     * @var Cookie
     */
    private Cookie $cookie;

    /**
     * Session constructor.
     * @param SessionDriverInterface $driver
     * @throws \Exception
     */
    public function __construct(SessionDriverInterface $driver)
    {
        $this->driver = $driver;
        $this->cookie = new Cookie();
        $this->generateSessionKey();
        $this->driver->setIp((string) Core::di()->request->getServerParam('REMOTE_ADDR'));
        $this->driver->setUserAgent(Core::di()->request->getServerParam('HTTP_USER_AGENT') ?? 'No user agent');
    }

    /**
     * @return SessionDriverInterface
     */
    public function getDriver(): SessionDriverInterface
    {
        return $this->driver;
    }

    /**
     * Purge session
     * @throws \Exception
     */
    public function purge(): void
    {
        $this->cookie->unset(Core::di()->config['app_name']);
        $this->driver->delete();
    }

    /**
     * @param string $key
     * @param null   $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return  $this->driver->get($key, $default);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->driver->toArray();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->key ?? '';
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        $this->driver->set($key, $value);
        $this->driver->save();
    }

    /**
     * @throws \Exception
     */
    private function generateSessionKey(): void
    {
        $this->key = $this->cookie->get($appName = Core::di()->config->get('app_name'), null);

        if ($this->key === null) {
            $this->key = Helpers::GUIDv4();
            $this->cookie->set((string) Core::di()->config->get('app_name'), $this->key, Cookie::ONE_DAY * 30);
        } else {
            $this->key = $this->cookie->get(Core::di()->config['app_name']);
        }

        $this->driver->setSessionIdentifier($this->key);
    }
}
