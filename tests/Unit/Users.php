<?php

namespace Test\Unit;


use Xwero\ComposableQueries\ReplacementInterface;

enum Users implements ReplacementInterface
{
    case Users;
    case Name;
    case Email;
}
