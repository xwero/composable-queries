<?php

declare(strict_types=1);

use Test\Unit\Users;
use Xwero\ComposableQueries\IdentifierInterface;
use Xwero\ComposableQueries\QueryParametersCollection;

test('single value', function (IdentifierInterface $key, mixed $value) {
    $collection = new QueryParametersCollection($key, $value);

    expect($collection->keyExists($key))->toBeTrue()
        ->and($collection->getValue($key))->toBe($value);
})->with([
    'string' => [
        Users::Name, 'me'
    ],
    'int' => [
       Users::Id, 1
    ],
    'float' => [
        Users::Id, 1.1
    ],
]);

test('bad single value', function () {
    $collection = new QueryParametersCollection(Users::Id, [1, 2, 3]);

   expect($collection->keyExists(Users::Id))->toBeFalse();
});

test('array', function (array $pair, array|null $result) {
    $collection = new QueryParametersCollection($pair);

    expect($collection->getValue((string) array_key_first($pair)))->toBe($result);
})->with([
    'good' => [
        ['Array:test' => [1,2]], [1,2]
    ],
    'empty because key is not string' => [
        [1 => [1,2]] , null
    ],
    'empty because value is no array' => [
        ['Array:test' => 1], null
    ],
]);