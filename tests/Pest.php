<?php

use Test\TestCase;
use Xwero\ComposableQueries\PDO\Connection;

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

function PdoUsers(string $query = "") : Connection
{
    $connection = new Connection(new PDO('sqlite::memory:'));

    $connection->connection->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    password TEXT NOT NULL
                                 );");

    if($query !== "") {
        $connection->connection->exec($query);
    }

    return $connection;
}



