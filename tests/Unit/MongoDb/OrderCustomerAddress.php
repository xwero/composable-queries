<?php

declare(strict_types=1);

namespace Test\Unit\MongoDb;

use Xwero\ComposableQueries\IdentifierInterface;

enum OrderCustomerAddress implements IdentifierInterface
{
    case Street;
    case City;
    case State;
    case Zip;
}