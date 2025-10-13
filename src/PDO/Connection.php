<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\PDO;

use PDO;
use Xwero\ComposableQueries\DatabaseConnectionException;
use Xwero\ComposableQueries\DatabaseConnectionInterface;

final class Connection implements DatabaseConnectionInterface
{
    public mixed $connection {
        set {
            if( ! $value instanceof PDO) {
                throw new DatabaseConnectionException('PDO connection must be instance of PDO');
            }

            $value->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connection = $value;
        }
        get => $this->connection;
    }
}