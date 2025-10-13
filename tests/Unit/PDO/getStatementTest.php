<?php

use Test\Unit\Users;
use Xwero\ComposableQueries\QueryParametersCollection;
use function Xwero\ComposableQueries\PDO\getStatement;


test('simple statement', function () {
   $query = 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users';
   $connection = PdoUsers();

   $statement = getStatement($connection, $query);

   expect($statement->queryString)->toBe('SELECT name FROM users');
});

test('statement with parameter', function () {
    $query = 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users WHERE ~Test\Unit\Users:Name = :Test\Unit\Users:Name';
    $connection = PdoUsers();
    $statement = getStatement($connection, $query, new QueryParametersCollection(Users::Name, 'me'));

    expect($statement->queryString)->toBe("SELECT name FROM users WHERE name = :Test_Unit_Users_Name");
});
