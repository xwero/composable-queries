<?php

use Test\Unit\Users;
use function Xwero\ComposableQueries\createMapFromQueryResult;


test('single item map', function () {
    $map = createMapFromQueryResult(['name' => 'John Doe'], 'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users;');

    expect($map)->toBeInstanceOf(SplObjectStorage::class)
        ->and($map->count())->toBe(1)
        ->and($map[Users::Name])->toBe('John Doe');
});