<?php

namespace Test\Unit;

use Xwero\ComposableQueries\ReplacementInterface;


enum UsersBacked : string implements ReplacementInterface
{
    case Users = 'users';
    case Name = 'name';
    case Email = 'e-mail';
}
