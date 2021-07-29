<?php

declare(strict_types=1);

namespace Siteworx\Cli;

use Dotenv\Dotenv;
use League\CLImate\CLImate;
use League\CLImate\Exceptions\InvalidArgumentException;
use Siteworx\Cli\Commands\{CommandInterface,
    Cron\RunCron,
    GetRoutes,
    OAuth\AddClientRedirectUri,
    OAuth\CreateClient,
    OAuth\CreateUser,
    OAuth\GenerateKey,
    Queue\StartConsumer};
use Siteworx\Library\Application\Core;

/**
 * Class CliTask
 *
 * @package Siteworx\Cli
 */
final class Kernel
{

    private const COMMANDS = [
        RunCron::class,
        StartConsumer::class,
        GenerateKey::class,
        GetRoutes::class,
        CreateClient::class,
        CreateUser::class,
        AddClientRedirectUri::class
    ];

    /**
     * @var CLImate
     */
    protected CLImate $cli;

    /**
     * CliTask constructor.
     */
    public function __construct()
    {
        $dotEnv = Dotenv::createMutable(__DIR__ . '/../../');
        $dotEnv->load();

        $this->cli = new CLImate();
        $this->printCopyright();
    }

    private function printCopyright(): void
    {
        $this->cli->border('-', 65)->br();
        $this->cli->tab()->bold('<blue>' . Core::di()->config->get('app_name') . '</blue>');
        $this->cli->tab()->info('Copyright (c) ' . date('Y') . ' Siteworx Professionals LLC.');
        $this->cli->tab()->info('Ron Rise <ron@siteworxpro.com>');
        $this->cli->tab()->info('Version: ' . Core::di()->config->get('settings.deployment'))->br();
        $this->cli->border('-', 65)->br();
    }

    /**
     * @param string $task
     */
    public function handle(string $task): void
    {
        $startTime = microtime(true);

        if ($task === '') {
            $this->cli->error('Error! Invalid arguments.');
            $this->cli->out('Usage: cli [job] [params]');
            $this->printUsage();
            exit(1);
        }

        $commandClass = null;

        /** @var CommandInterface $command */
        foreach (self::COMMANDS as $command) {
            if ($command::commandSignature() === str_replace('--', '', $task)) {
                $commandClass = $command;

                break;
            }
        }

        if ($commandClass === null) {
            $this->cli->error('Error! Not a task. Did you register it correctly?');
            $this->printUsage();
            exit(1);
        }

        try {
            /** @var CommandInterface $job */
            $job = new $commandClass();
        } catch (InvalidArgumentException $exception) {
            $this->cli->error($exception->getMessage());
            exit(1);
        }

        $this->cli->info('Starting job....');

        try {
            $code = $job->execute();
        } catch (\Exception $exception) {
            $this->cli->error($exception->getMessage());
            $this->cli->info($exception->getTraceAsString());
            $code = $exception->getCode();

            if (!$code) {
                $code = 1;
            }
        } catch (\Throwable $exception) {
            $this->cli->error($exception->getMessage());
            $this->cli->info($exception->getTraceAsString());
            $code = $exception->getCode();

            if (!$code) {
                $code = 1;
            }
        }

        if ($code !== 0) {
            $this->cli->out('<red><blink>Error!!</blink> Job <bold>FAILED!</bold></red>')->br();
        } else {
            $this->cli->out('<green>Job Completed <bold>Successfully!</bold></green>');
        }

        $time = microtime(true) - $startTime;

        $this->cli->out('<blue>Total Execution Time: ' . round($time, 5) . 's')->br();
        exit($code);
    }

    private function printUsage(): void
    {
        /** @var CommandInterface $command */
        foreach (self::COMMANDS as $command) {
            $this->cli->info('--' . $command::commandSignature());
            $this->cli->tab()->info($command::getHelp())->br();
        }
    }
}
