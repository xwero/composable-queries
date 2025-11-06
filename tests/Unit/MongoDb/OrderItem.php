<?php

namespace Test\Unit\MongoDb;

use Xwero\ComposableQueries\IdentifierInterface;

enum OrderItem implements IdentifierInterface
{
    case Sku;
    case Name;
    case Quantity;
    case Price;
}
