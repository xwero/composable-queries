<?php

namespace Test\Unit;

use Xwero\ComposableQueries\IdentifierInterface;

enum PrincipalCharacters : string implements IdentifierInterface
{
    case PrincipalCharacters = 'principal_character';
    case TitleId = 'tconst';
    case PersonId = 'nconst';
    case Character = 'character';
}
