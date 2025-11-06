<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\MongoDb;

use MongoDB\Client;
use MongoDB\Database;

final readonly class Connection implements MongoDbConnectionInterface
{
    public Database $databaseClient;

    public function __construct(public Client $client, public string $database)
    {
        $this->databaseClient = $this->client->getDatabase($database);
    }
}