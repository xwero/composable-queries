<?php

use MongoDB\Client;
use Test\TestCase;
use Xwero\ComposableQueries\MongoDb\Connection;
use Xwero\ComposableQueries\PDO\Connection as PDOConnection;
use Xwero\ComposableQueries\Predis\Command;
use Xwero\ComposableQueries\Predis\Connection as PredisConnection;
use function Xwero\ComposableQueries\Predis\executeCommand;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)->in('Feature');

function PdoUsers(string $query = "") : PDOConnection
{
    $connection = new PDOConnection(new PDO('sqlite::memory:'));

    $connection->client->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    password TEXT NOT NULL
                                 );");

    if($query !== "") {
        $connection->client->exec($query);
    }

    return $connection;
}

function RedisConnection(Command ...$commands) : PredisConnection
{
    $port = getenv('REDIS_PORT');

    if($port === false) {
        $port = 6380;
    }

    $connection = new PredisConnection(new Predis\Client('tcp://127.0.0.1:' . $port));

    try {
        $connection->client->ping();
    } catch (Exception $e) {
        throw new Exception("Redis server is not running.");
    }

    if(count($commands) > 0){
        foreach ($commands as $command) {
            executeCommand($connection, $command);
        }
    }

    return $connection;
}


function MongoDbTestDb(array|null $document = null, string|null $collection = null) : Connection
{
    $connection = new Connection(new Client(), 'test');

    if(is_array($document) && is_string($collection)) {
        $connection->databaseClient->getCollection($collection)->insertOne($document);
    }

    return $connection;
}


