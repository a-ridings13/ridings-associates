<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\OAuth;

use League\CLImate\TerminalObject\Dynamic\Input;
use Siteworx\Cli\Commands\Command;
use Siteworx\Library\OAuth\Entities\Client;
use Siteworx\Library\Utilities\Helpers;

/**
 * Class CreateClient
 * @package Siteworx\Cli\Commands\OAuth
 */
final class CreateClient extends Command
{

    public function __construct()
    {
        parent::__construct();

        $this->cli->arguments->add([
            'clientName' => [
                'longPrefix' => 'client',
                'prefix' => 'c'
            ],
            'grant' => [
                'longPrefix' => 'grant',
                'prefix' => 'g'
            ],
            'internal' => [
                'longPrefix' => 'internal',
                'prefix' => 'i',
                'noValue' => true
            ],
            'writeToEnv' => [
                'longPrefix' => 'write',
                'prefix' => 'w',
                'noValue' => true
            ]
        ]);

        $this->cli->arguments->parse();
    }

    /**
     * @inheritDoc
     */
    public static function getHelp(): string
    {
        return 'Generate oauth client';
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(): int
    {
        if ($this->cli->arguments->get('clientName') === '') {
            /** @var Input $prompt */
            $prompt = $this->cli->input('Client Name: ');
            $clientName = $prompt->prompt();
        } else {
            $clientName = $this->cli->arguments->get('clientName');
        }

        $clientId = Helpers::generateRandString(32);
        $clientSecret = Helpers::generateRandString(64);

        if (!\in_array($this->cli->arguments->get('grant'), Client::grants(), true)) {
            /** @var Input $prompt */
            $prompt = $this->cli->input('Grant (' . implode(', ', Client::grants()) . '): ');
            $prompt->accept(Client::grants());
            $grant = $prompt->prompt();
        } else {
            $grant = $this->cli->arguments->get('grant');
        }

        $confidential = 'y';

        if (($grant === Client::AUTHORIZATION_CODE) && $this->cli->arguments->get('internal') !== true) {
            /** @var Input $prompt */
            $prompt = $this->cli->input('Internal Client? (Y/n): ');
            $prompt->accept(['y', 'n', '']);
            $confidential = $prompt->prompt();
        }

        $confidential = $confidential !== 'n';

        $client = new Client();
        $client->client_id = $clientId;
        $client->client_secret = $clientSecret;
        $client->client_name = $clientName;
        $client->grant_type = $grant;
        $client->confidential = $confidential;
        $client->save();

        $this->cli->info('Client ID: ' . $clientId);
        $this->cli->info('Client Secret: ' . $clientSecret);

        if ($this->cli->arguments->get('writeToEnv')) {
            if (!file_exists('./.env')) {
                file_put_contents('./.env', file_get_contents('./.env.template'));
            }

            file_put_contents('./.env', file_get_contents('./.env') . sprintf("\nCLIENT_ID=%s\n", $clientId));
            file_put_contents('./.env', file_get_contents('./.env') . sprintf("CLIENT_SECRET=%s\n", $clientSecret));
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public static function commandSignature(): string
    {
        return 'generate-oauth-client';
    }
}
