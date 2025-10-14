<?php

namespace Test\Unit;

use Xwero\ComposableQueries\IdentifierInterface;


enum UsersBacked : string implements IdentifierInterface
{
    case Users = 'users';
    case Name = 'name';
    case Email = 'e-mail';
}
