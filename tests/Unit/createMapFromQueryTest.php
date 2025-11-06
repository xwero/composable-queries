<?php

use Test\Unit\Users;
use Xwero\ComposableQueries\AliasCollection;
use Xwero\ComposableQueries\Error;
use function Xwero\ComposableQueries\createMapFromQueryResult;

test('error', function () {
   $map = createMapFromQueryResult(new Error(new Exception('test')), '');

   expect($map)->toBeInstanceOf(Error::class);
});

test('single item map', function () {
    $map = createMapFromQueryResult(
        ['name' => 'John Doe'],
        'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users;'
    );

    expect($map)->toBeInstanceOf(SplObjectStorage::class)
        ->and($map->count())->toBe(1)
        ->and($map[Users::Name])->toBe('John Doe');
});

test('alias map', function () {
    $map = createMapFromQueryResult(
        ['cname' => 'John Doe'],
        'SELECT ~Test\Unit\Users:Name AS cname FROM ~Test\Unit\Users:Users;',
        new AliasCollection('cname', Users::Name),
    );

    expect($map[Users::Name])->toBe('John Doe');
});