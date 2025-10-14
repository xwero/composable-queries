<?php

namespace Test\Unit;


use Xwero\ComposableQueries\IdentifierInterface;

enum Users implements IdentifierInterface
{
    case Users;
    case Name;
    case Email;
}
