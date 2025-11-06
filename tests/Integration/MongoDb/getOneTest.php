<?php

declare(strict_types=1);

use Xwero\ComposableQueries\Error;
use function Xwero\ComposableQueries\MongoDb\getOne;

test('error', function () {
   $result = getOne(MongoDbTestDb(), 'test', new Error(new Exception('test')));

   expect($result)->toBeInstanceOf(Error::class);
});

test('return single document', function () {
   $identifier =  ['name' => 'test'];
   $connection = MongoDbTestDb($identifier, 'test');
   $result = getOne($connection, 'test', $identifier);

   expect($result)->toHaveKey('name')
       ->and($result['name'])->toBe('test');
});