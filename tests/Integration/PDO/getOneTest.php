<?php

use Test\Unit\Users;
use Xwero\ComposableQueries\QueryParametersCollection;
use Xwero\ComposableQueries\Error;
use function Xwero\ComposableQueries\PDO\getOne;
use function Xwero\ComposableQueries\PDO\getStatement;

test('error', function () {
    expect(getOne(new Error(new Exception('test'))))->toBeInstanceOf(Error::class);
});

test('get name', function () {
   $pdo = PdoUsers("INSERT INTO users (name, email, password) VALUES ('me', 'dfsdf', 'dfsdf')");
   $query = 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users WHERE ~Test\Unit\Users:Name = :Test\Unit\Users:Name';
   $statement = getStatement($pdo, $query, new QueryParametersCollection(Users::Name, 'me'));

   expect(getOne($statement))->toBe('me');
});