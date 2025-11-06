<?php

namespace Xwero\ComposableQueries\MongoDb;

use MongoDB\Client;

interface MongoDbConnectionInterface
{
    function __construct(Client $client, string $database);
}