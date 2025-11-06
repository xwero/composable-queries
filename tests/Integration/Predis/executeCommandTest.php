<?php

declare(strict_types=1);

use Xwero\ComposableQueries\Error;
use Xwero\ComposableQueries\Predis\Command;
use function Xwero\ComposableQueries\Predis\executeCommand;

test('error', function () {
    $result = executeCommand(RedisConnection(), new Error(new Exception('test')));

    expect($result)->toBeInstanceOf(Error::class);
});

test('get key', function ()
{
    $result =executeCommand(
        RedisConnection(new Command('del', ['test']), new Command('set', ['test', 1])),
        new Command('get', ['test']),
    );

    expect($result)->toBe('1');
});