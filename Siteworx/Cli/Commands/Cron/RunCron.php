<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\Cron;

use Siteworx\Cli\Commands\Command;
use Siteworx\Library\Application\Core;
use Siteworx\Library\Cron\{CronException, JobInterface};

/**
 * Class RunCron
 * @package Siteworx\Cli\Commands\Cron
 */
class RunCron extends Command
{

    public function __construct()
    {
        parent::__construct();

        $this->cli->arguments->add([
            'job' => [
                'prefix' => 'j',
                'longPrefix' => 'job',
            ]
        ]);

        $this->cli->arguments->parse();
    }

    /**
     * @return string
     */
    public static function getHelp(): string
    {
        return 'run scheduled jobs';
    }

    /**
     * @return int Return exit code
     * @throws CronException
     */
    public function execute(): int
    {
        $job = $this->cli->arguments->get('job');

        if ($job !== '' && $job !== null) {
            $fullClassName = 'Siteworx\\Library\\Cron\\Jobs\\' . $job;
            /** @var JobInterface $class */
            $class = new $fullClassName();
            $class->run();

            return 0;
        }

        $path = Core::di()->config->get('run_dir') . '/Siteworx/Library/Cron/Jobs';

        $files = scandir($path, SORT_ASC);

        $skip = [
            '.', '..', 'Job', 'JobInterface'
        ];

        foreach ($files as $file) {
            $className = str_replace('.php', '', $file);

            if (\in_array($className, $skip, true)) {
                continue;
            }

            $fullClassName = 'Siteworx\\Library\\Cron\\Jobs\\' . $className;

            try {
                /** @var JobInterface $class */
                $class = new $fullClassName();
                $class->checkAndRun();
            } catch (\Throwable $exception) {
                Core::di()->logCron->critical($fullClassName);
                Core::di()->logCron->critical($exception->getMessage());
                Core::di()->logCron->critical($exception->getTraceAsString());
            }
        }

        return 0;
    }

    /**
     * @return string
     */
    public static function commandSignature(): string
    {
        return 'cron';
    }
}
