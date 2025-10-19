<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\Predis;


use Predis\Client;
use Xwero\ComposableQueries\DatabaseConnectionException;
use Xwero\ComposableQueries\DatabaseConnectionInterface;

final class Connection implements DatabaseConnectionInterface
{

    public function __construct(public mixed $connection)
    {
        if( ! $connection instanceof Client) {
            throw new DatabaseConnectionException('PDO connection must be instance of PDO');
        }
    }
}