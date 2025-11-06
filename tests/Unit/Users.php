<?php

namespace Test\Unit;


use Xwero\ComposableQueries\IdentifierInterface;

enum Users implements IdentifierInterface
{
    case Users;
    case Id;
    case Name;
    case Email;
}
