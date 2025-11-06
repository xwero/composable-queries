<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\PDO;

use PDO;

final readonly class Connection implements PDOConnectionInterface
{

    public function __construct(public PDO $client)
    {
        $client->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}