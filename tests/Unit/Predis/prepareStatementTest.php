<?php

use Test\Unit\Users;
use Xwero\ComposableQueries\Predis\Command;
use Xwero\ComposableQueries\QueryParametersCollection;
use function Xwero\ComposableQueries\Predis\getStatement;

test('single argument', function (string $query, Command $command) {
    $result = getStatement($query);

    expect($result)->toBeInstanceOf(Command::class)
        ->and($result->name)->toBe($command->name)
        ->and($result->arguments)->toBe($command->arguments);

})->with([
    'get key' => ['get ~Test\Unit\Users:Name', new Command('get', ['name'])],
]);

test('single argument with parameter', function (string $query, QueryParametersCollection $parameters, Command $command) {
    $result = getStatement($query, $parameters);

    expect($result)->toBeInstanceOf(Command::class)
        ->and($result->name)->toBe($command->name)
        ->and($result->arguments)->toBe($command->arguments);

})->with([
    'set key' => [
        'set ~Test\Unit\Users:Name :Test\Unit\Users:Name',
        new QueryParametersCollection(Users::Name, 'me'),
        new Command('set', ['name', 'me']),
    ],
]);

test('multiple arguments', function (string $query, Command $command) {
    $result = getStatement($query);

    expect($result)->toBeInstanceOf(Command::class)
        ->and($result->name)->toBe($command->name)
        ->and($result->arguments)->toBe($command->arguments);
})->with([
    'add multiple items to list' => [
        'rpush ~Test\Unit\Users:Users ~Test\Unit\Users:Name:1 ~Test\Unit\Users:Name:2',
        new Command('rpush', ['users', 'name:1', 'name:2']),
    ],
]);


