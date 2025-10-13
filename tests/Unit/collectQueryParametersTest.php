<?php

use Test\Unit\Users;
use Test\Unit\UsersBacked;
use Xwero\ComposableQueries\BaseNamespaceCollection;
use Xwero\ComposableQueries\QueryParametersCollection;
use function Xwero\ComposableQueries\collectQueryParameters;

test ('parameters', function (
                                string $query,
                                QueryParametersCollection $parameters,
                                BaseNamespaceCollection|null $baseNamespaces,
                                array $result,
    ) {
    expect(collectQueryParameters($query, $parameters, $baseNamespaces))->toBe($result);
})->with([
    'full namespace' => [
        ':Test\Unit\Users:Users',
        new QueryParametersCollection(Users::Users, 1),
        null,
        [':Test\Unit\Users:Users' => 1]
    ],
    'bad full namespace' => [
        ':Test\Unit\Users:UsersBad',
        new QueryParametersCollection(Users::Users, 1),
        null,
        []
    ],
    'shortend namespace' =>[
        ':Users:Users',
        new QueryParametersCollection(Users::Users, 1),
        new BaseNamespaceCollection('Test\Unit'),
        [':Users:Users' => 1]
    ],
    'shortend namespace multiple replacements' =>[
        ':Users:Users, :UsersBacked:Users',
        new QueryParametersCollection(Users::Users, 1, UsersBacked::Users, 2),
        new BaseNamespaceCollection('Test\Unit'),
        [':Users:Users' => 1, ':UsersBacked:Users' => 2]
    ],
    'array' => [
        ':Array:Test',
        new QueryParametersCollection([':Array:Test' => [1,2,3]]),
        null,
        [':Array:Test_0' => 1, ':Array:Test_1' => 2, ':Array:Test_2' => 3]
    ],
    'bad array key' => [
        ':Array:Test',
        new QueryParametersCollection([1 => [1,2,3]]),
        null,
        []
    ],
    'bad array value' => [
        ':Array:Test',
        new QueryParametersCollection([':Array:Test' => 1]),
        null,
        []
    ],
]);