<?php

namespace Test\Unit\MongoDb;

use Xwero\ComposableQueries\IdentifierInterface;

enum Orders implements IdentifierInterface
{
    case Orders;
    case OrderId;
    case Customer;
    case Items;
    case Status;
    case Created;
}
