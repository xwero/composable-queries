<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

interface DatabaseConnectionInterface
{
    protected(set) mixed $connection {
        get;
        set;
    }
}