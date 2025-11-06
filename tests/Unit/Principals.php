<?php

namespace Test\Unit;

use Xwero\ComposableQueries\IdentifierInterface;

enum Principals : string implements IdentifierInterface
{
    case Principals = 'principal';
    case TitleId = 'tconst';
    case PersonId = 'nconst';
    case Category = 'category';
    case Order = 'ordering';
}
