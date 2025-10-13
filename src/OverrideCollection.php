<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

class OverrideCollection extends TypeCollection
{
    public function __construct(ReplacementInterface|string ...$pairs)
    {
        $keys = array_filter($pairs, fn($item) => $item instanceof ReplacementInterface);
        $values = array_filter($pairs, fn($item) => is_string($item));
        // Having more values than keys means storing too much information.
        if (count($keys) < count($values)) {
            $values = array_slice($values, 0, count($keys));
        }

        $this->keys = array_values($keys);
        $this->values = array_values($values);
    }

    public function getValue(ReplacementInterface $check) : string
    {
        $valueKey = array_search($check, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : '';
    }
}