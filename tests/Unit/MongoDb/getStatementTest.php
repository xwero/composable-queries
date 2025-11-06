<?php

declare(strict_types=1);


use Xwero\ComposableQueries\BaseNamespaceCollection;
use Xwero\ComposableQueries\Error;
use Xwero\ComposableQueries\JSONException;
use function Xwero\ComposableQueries\MongoDb\getStatement;

test('error', function () {
    $statement = getStatement('{~Users:Name: "me"}');

    expect($statement)->toBeInstanceOf(Error::class)
    ->and($statement->exception)->toBeInstanceOf(JSONException::class);
});

test('no parameters', function () {
   $statement = getStatement('{"~Users:Name": "me"}', namespaces: new BaseNamespaceCollection('Test\Unit'));

   expect($statement)->toBe(['name' => 'me']);
});