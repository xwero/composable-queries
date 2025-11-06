<?php

namespace Test\Unit;

use Xwero\ComposableQueries\IdentifierInterface;

enum Titles : string implements IdentifierInterface
{
    case Titles = 'title';
    case Id = 'tconst';
    case Title = 'primary_title';
    case Genres = 'genres';
}
