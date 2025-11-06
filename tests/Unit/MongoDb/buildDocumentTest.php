<?php

declare(strict_types=1);

use Test\Unit\MongoDb\OrderCustomer;
use Test\Unit\MongoDb\Orders;
use Xwero\ComposableQueries\MongoDb\DocumentBranch;
use function Xwero\ComposableQueries\MongoDb\buildDocument;

test('single level document', function (array $branches, $result) {
    expect(buildDocument(...$branches))->toBe($result);
})->with([
    [
        [
            new DocumentBranch(Orders::OrderId, 1),
            new DocumentBranch(Orders::Status, 'pending'),
        ],
        ['orderid' => 1, 'status' => 'pending']
    ]
]);

test('multi level document', function (array $branches, $result) {
    expect(buildDocument(...$branches))->toBe($result);
})->with([
    [
        [
            new DocumentBranch(Orders::OrderId, 1),
            new DocumentBranch(OrderCustomer::Name, 'me', 'customer'),
        ],
        ['orderid' => 1, 'customer' => ['name' => 'me']],
    ]
]);