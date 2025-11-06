<?php

namespace Test\Unit\MongoDb;

use Xwero\ComposableQueries\IdentifierInterface;

enum OrderCustomer implements IdentifierInterface
{
    case Name;
    case Email;
    case Address;
}
