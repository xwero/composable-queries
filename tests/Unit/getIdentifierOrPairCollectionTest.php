<?php

use Test\Unit\Users;
use Xwero\ComposableQueries\BaseNamespaceCollection;
use function Xwero\ComposableQueries\queryToIdentifierOrPairCollection;

test('', function(string $query, BaseNamespaceCollection|null $namespaces, array $result) {
    expect(queryToIdentifierOrPairCollection($query, "(~[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $namespaces))->toBe($result);
})->with([
    'pair match' => [
      '~Test:A',
      null,
      [
          ['~Test:A', ['Test', 'A']]
      ]
    ],
    'full namespace' => [
        'SELECT ~Test\Unit\Users:Name FROM ~Test\Unit\Users:Users;',
        null,
        [
            ['~Test\Unit\Users:Name', Users::Name],
            ['~Test\Unit\Users:Users', Users::Users],
        ],
    ],
    'shortend namespace' => [
        'SELECT ~Users:Name FROM ~Users:Users;',
        new BaseNamespaceCollection('Test\Unit'),
        [
            ['~Users:Name', Users::Name],
            ['~Users:Users', Users::Users],
        ],
    ],
]);