<?php

namespace Test\Unit;

use Xwero\ComposableQueries\IdentifierInterface;

enum Persons : string implements IdentifierInterface
{
    case Persons = 'person';
    case Id = 'nconst';
    case Name = 'primaryName';

}
