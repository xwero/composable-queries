<?php

use Test\Unit\Users;
use function Xwero\ComposableQueries\createMapFromArray;

test('single item map', function () {
    $map = createMapFromArray(Users::Users, ['name' => 'John Doe']);

    expect($map)->toBeInstanceOf(SplObjectStorage::class)
        ->and($map->count())->toBe(1)
        ->and($map[Users::Name])->toBe('John Doe');
});