<?php

use function Xwero\ComposableQueries\PDO\getAll;
use function Xwero\ComposableQueries\PDO\getStatement;

test('get names', function () {
   $pdo = PdoUsers("INSERT INTO users (name, email, password) VALUES ('me', 'dfsdf', 'dfsdf'), ('metwo', 'dfsdf', 'dfsdf')");
   $query = 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users';
   $statement = getStatement($pdo, $query);

   expect(getAll($statement))->toBe([['name' => 'me'], ['name' => 'metwo']]);
});