<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

class BaseNamespaceCollection extends BaseCollection
{

    public function __construct(string ...$namespaces)
    {
        foreach ($namespaces as $namespace) {
            $this->collection[] = $namespace;
        }
    }
}