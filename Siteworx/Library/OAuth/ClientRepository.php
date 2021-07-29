<?php

declare(strict_types=1);

namespace Siteworx\Library\OAuth;

use Siteworx\Library\OAuth\Entities\Client;
use League\OAuth2\Server\{Entities\ClientEntityInterface, Repositories\ClientRepositoryInterface};

final class ClientRepository implements ClientRepositoryInterface
{

    /**
     * Validate a client's secret.
     *
     * @param string $clientIdentifier The client's identifier
     * @param null|string $clientSecret The client's secret (if sent)
     * @param null|string $grantType The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        /** @var Client $client */
        $client = Client::where('client_id', '=', $clientIdentifier)->get()->first();

        if ($client === null) {
            return false;
        }

        return $client->client_id === $clientIdentifier &&
            $client->client_secret === $clientSecret &&
            $client->grant_type === $grantType;
    }

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     *
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier): ?Client
    {
        return Client::where('client_id', '=', $clientIdentifier)->get()->first();
    }
}
