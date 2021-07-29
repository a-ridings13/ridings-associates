<?php

declare(strict_types=1);

namespace Siteworx\Library\Application;

use Aws\S3\S3Client;
use Carbon\Carbon;
use League\Flysystem\{Adapter\Local, AwsS3v3\AwsS3Adapter, Filesystem};
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;
use League\Tactician\CommandBus;
use League\Tactician\Handler\{CommandHandlerMiddleware,
    CommandNameExtractor\ClassNameExtractor,
    Locator\InMemoryLocator,
    MethodNameInflector\HandleInflector};
use Monolog\{Formatter\LineFormatter,
    Handler\StreamHandler,
    Logger,
    Processor\MemoryUsageProcessor,
    Processor\WebProcessor};
use Noodlehaus\Config;
use Pimple\Container as Pimple;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;
use Siteworx\Library\Application\Exceptions\InvalidContainerItemException;
use Siteworx\Library\Http\{Request, RequestFactory, Response, ResponseFactory};
use Siteworx\Library\Session\{Drivers\Mysql, Session};
use Siteworx\Library\OAuth\AccessTokenRepository;
use Siteworx\Library\OAuth\AuthCodeRepository;
use Siteworx\Library\OAuth\ClientRepository;
use Siteworx\Library\OAuth\Entities\AccessToken;
use Siteworx\Library\OAuth\Entities\Client;
use Siteworx\Library\OAuth\Entities\User;
use Siteworx\Library\OAuth\RefreshTokenRepository;
use Siteworx\Library\OAuth\ScopeRepository;
use Siteworx\Library\Twig;
use Twig\Loader\FilesystemLoader;

/**
 * Class Container
 *
 * @property Config config
 * @property Twig view
 * @property Logger logQueue
 * @property Logger logCron
 * @property Logger log
 * @property CommandBus commandBus
 * @property Filesystem filesystem
 * @property Request|RequestInterface request
 * @property Response|ResponseInterface|ResponseFactoryInterface response
 * @property Session session
 * @property AuthorizationServer oauthserver
 * @property ResourceServer resourceserver
 * @property Client client
 * @property User user
 * @property AccessToken accessToken
 * @property \Memcached cache
 *
 * @package Siteworx\Siteworx\Library
 */
final class Container extends Pimple implements ContainerInterface
{

    /**
     * @var bool
     */
    private bool $booted = false;

    public function __construct(array $values = array())
    {
        parent::__construct($values);

        if ($this->booted === false) {
            $this->bootstrap();
        }
    }

    private function bootstrap(): void
    {
        $this['response'] = static function () {
            return new ResponseFactory();
        };

        $this['request'] = static function () {
            return RequestFactory::createFromGlobals();
        };

        /*
        |--------------------------------------------------------------------------
        | Config
        |--------------------------------------------------------------------------
        */
        $this['config'] = static function () {
            $path = __DIR__ . '/../../../var/config/config.php';

            return Config::load($path);
        };

        /*
        |--------------------------------------------------------------------------
        | Memcached
        |--------------------------------------------------------------------------
        */
        $this['cache'] = function () {
            $cached = new \Memcached($this->config->get('memcache.app_key') . '.');
            $cached->addServer(
                $this->config->get('memcache.server'),
                $this->config->get('memcache.port')
            );

            return $cached;
        };

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */
        $this['view'] = function () {
            $loader = new FilesystemLoader($this->config->get('run_dir') . '/Siteworx/Views');
            $twig = new Twig($loader, [
                'cache' => $this->config->get('run_dir') . '/var/cache/views',
                'auto_reload' => $this->config->get('dev_mode', false)
            ]);

            $epoch = file_get_contents($this->config->get('run_dir') . '/.epoch');

            if ($epoch === false) {
                $epoch = (string) Carbon::now()->timestamp;
            }

            $twig->addGlobal('config', $this->config);
            $twig->addGlobal('year', Carbon::now()->format('Y'));
            $twig->addGlobal('epoch', trim($epoch));

            return $twig;
        };

        /*
        |--------------------------------------------------------------------------
        | Log
        |--------------------------------------------------------------------------
        */
        $this['log'] = function () {
            $logger = new Logger('App');

            $formatter = new LineFormatter(
                null,
                null,
                true,
                true
            );

            $handler = new StreamHandler(
                $this->config->get('run_dir') . '/var/logs/app.log',
                $this->config->get('log.log_level', LogLevel::DEBUG)
            );
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            $logger->pushProcessor(new WebProcessor());
            $logger->pushProcessor(new MemoryUsageProcessor());

            return $logger;
        };

        /*
        |--------------------------------------------------------------------------
        | logQueue
        |--------------------------------------------------------------------------
        */
        $this['logQueue'] = function () {
            $logger = new Logger('App');

            $formatter = new LineFormatter(
                null,
                null,
                true,
                true
            );

            $handler = new StreamHandler(
                $this->config->get('run_dir') . '/var/logs/queue.log',
                $this->config->get('log.log_level', LogLevel::DEBUG)
            );
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $logger->pushProcessor(new MemoryUsageProcessor());

            return $logger;
        };

        /*
        |--------------------------------------------------------------------------
        | logCron
        |--------------------------------------------------------------------------
        */
        $this['logCron'] = function () {
            $logger = new Logger('App');

            $formatter = new LineFormatter(
                null,
                null,
                true,
                true
            );

            $handler = new StreamHandler(
                $this->config->get('run_dir') . '/var/logs/cron.log',
                $this->config->get('log.log_level', LogLevel::DEBUG)
            );
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            $logger->pushProcessor(new MemoryUsageProcessor());

            return $logger;
        };

        /*
        |--------------------------------------------------------------------------
        | Filesystem
        |--------------------------------------------------------------------------
        */
        $this['filesystem'] = function () {
            if ($this->config->get('app_env', 'prod') === 'vagrant') {
                $runDir = $this->config->get('run_dir') . '/var/cache/s3';
                $adapter = new Local($runDir);
            } else {
                $client = new S3Client($this->config['aws']['s3']);
                $adapter = new AwsS3Adapter(
                    $client,
                    $this->config->get('aws.bucket')
                );
            }

            return new Filesystem($adapter);
        };

        /*
        |--------------------------------------------------------------------------
        | Session
        |--------------------------------------------------------------------------
        */
        $this['session'] = static function () {
            $driver = new Mysql();

            return new Session($driver);
        };

        /*
        |--------------------------------------------------------------------------
        | Resource Server
        |--------------------------------------------------------------------------
        */
        $this['resourceserver'] = function (): ResourceServer {
            $accessTokenRepository = new AccessTokenRepository();
            $privateKey = $this->config->get('run_dir') . '/authorization.key';

            return  new ResourceServer(
                $accessTokenRepository,
                $privateKey
            );
        };

        /*
        |--------------------------------------------------------------------------
        | oAuth Server
        |--------------------------------------------------------------------------
        */
        $this['oauthserver'] = function () {
            $clientRepository = new ClientRepository();
            $scopeRepository = new ScopeRepository();
            $accessTokenRepository = new AccessTokenRepository();
            $privateKey = $this->config->get('run_dir') . '/authorization.key';
            $encryptionKey = $this->config->get('app_key');

            $server = new AuthorizationServer(
                $clientRepository,
                $accessTokenRepository,
                $scopeRepository,
                $privateKey,
                $encryptionKey
            );

            $server->enableGrantType(
                new ClientCredentialsGrant(),
                new \DateInterval('PT1H')
            );

            $server->enableGrantType(
                new AuthCodeGrant(
                    new AuthCodeRepository(),
                    new RefreshTokenRepository(),
                    new \DateInterval('PT1M')
                ),
                new \DateInterval('PT1H')
            );

            $server->enableGrantType(
                new RefreshTokenGrant(
                    new RefreshTokenRepository()
                ),
                new \DateInterval('PT1H')
            );

            return $server;
        };

        /*
        |--------------------------------------------------------------------------
        | Command Bus
        |--------------------------------------------------------------------------
        */
        $this['commandBus'] = static function () {
            $commands = [];

            $handlerMiddleware = new CommandHandlerMiddleware(
                new ClassNameExtractor(),
                new InMemoryLocator($commands),
                new HandleInflector()
            );

            return new CommandBus([$handlerMiddleware]);
        };

        $this->booted = true;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id): bool
    {
        try {
            return $this->offsetGet($id) !== null;
        } catch (UnknownIdentifierException $exception) {
            return false;
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws InvalidContainerItemException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param callable $value
     */
    public function __set(string $name, callable $value)
    {
        $this[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return $this[$name] !== null;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws InvalidContainerItemException|NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get($id)
    {
        try {
            return $this->offsetGet($id);
        } catch (UnknownIdentifierException $exception) {
            throw new InvalidContainerItemException($id);
        }
    }
}
