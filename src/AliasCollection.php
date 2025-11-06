<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

use Xwero\ComposableQueries\TypeCollection;

class AliasCollection extends TypeCollection
{
    public function __construct(IdentifierInterface|string ...$pairs)
    {
        $filteredKeys = array_filter($pairs, fn($item) => is_string($item));
        $filteredValues = array_filter($pairs, fn($item) => $item instanceof IdentifierInterface);
        // Having more values than keys means storing too much information.
        if (count($filteredKeys) < count($filteredValues)) {
            $filteredValues = array_slice($filteredValues, 0, count($filteredKeys));
        }

        $this->keys = array_values($filteredKeys);
        $this->values = array_values($filteredValues);
    }

    public function getIdentifier(string $key): IdentifierInterface|null
    {
        $valueKey = array_search($key, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : null;
    }
}