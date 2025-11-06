<?php

namespace Xwero\ComposableQueries\PDO;

use PDO;

interface PDOConnectionInterface
{
    function __construct(PDO $pdo);
}