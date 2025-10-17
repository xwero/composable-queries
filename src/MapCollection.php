<?php

namespace Xwero\ComposableQueries;

use SplObjectStorage;
use Xwero\ComposableQueries\BaseCollection;

class MapCollection extends BaseCollection
{
    public function __construct(SplObjectStorage ...$items)
    {
        foreach ($items as $item) {
            $this->collection[] = $item;
        }
    }

    public function append(SplObjectStorage $item): void
    {
        $this->collection[] = $item;
    }
}