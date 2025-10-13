<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

abstract class BaseCollection
{
    protected array $collection = [];

    public function getAll(): array
    {
        return $this->collection;
    }
}