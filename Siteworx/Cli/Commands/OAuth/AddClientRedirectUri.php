<?php

declare(strict_types=1);

namespace Siteworx\Cli\Commands\OAuth;

use League\CLImate\TerminalObject\Dynamic\Input;
use Siteworx\Cli\Commands\Command;
use Siteworx\Library\OAuth\Entities\Client;
use Siteworx\Library\OAuth\Entities\ClientRedirectDomain;

/**
 * Class AddClientRedirectUri
 * @package Siteworx\Cli\Commands\OAuth
 */
final class AddClientRedirectUri extends Command
{

    public function __construct()
    {
        parent::__construct();

        $this->cli->arguments->add([
            'client' => [
                'prefix' => 'c',
                'longPrefix' => 'client',
                'castTo' => 'int'
            ],
            'uri' => [
                'prefix' => 'u',
                'longPrefix' => 'uri'
            ]
        ]);

        $this->cli->arguments->parse();
    }

    /**
     * @inheritDoc
     */
    public static function getHelp(): string
    {
        return 'Add a valid redirect uri for a client';
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        $clients = Client::where('grant_type', '=', Client::AUTHORIZATION_CODE)->get();

        if ($clients->isEmpty()) {
            $this->cli->yellow('No clients available... Add one first!');

            return 1;
        }

        /** @var Client $client */
        foreach ($clients as $client) {
            $this->cli->info($client->id . "\t" . $client->client_name);
        }

        if (!\in_array($this->cli->arguments->get('client'), array_column($clients->toArray(), 'id'), true)) {
            /** @var Input $prompt */
            $prompt = $this->cli->input('Client Id: ');
            $prompt->accept(array_column($clients->toArray(), 'id'));
            $clientId = $prompt->prompt();
        } else {
            $clientId = $this->cli->arguments->get('client');
        }

        if ($this->cli->arguments->get('uri') === '') {
            /** @var Input $prompt */
            $prompt = $this->cli->input('URI: ');
            $uri = $prompt->prompt();
        } else {
            $uri = $this->cli->arguments->get('uri');
        }

        $clientRedirectDomain = new ClientRedirectDomain();
        $clientRedirectDomain->client_id = $clientId;
        $clientRedirectDomain->domain = $uri;
        $clientRedirectDomain->save();

        return 0;
    }

    /**
     * @inheritDoc
     */
    public static function commandSignature(): string
    {
        return 'add-client-redirect-uri';
    }
}
