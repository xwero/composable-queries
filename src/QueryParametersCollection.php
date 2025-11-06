<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

class QueryParametersCollection extends TypeCollection
{
    public function __construct(IdentifierInterface|string|int|float|array ...$pairs)
    {
        $keys = [];
        $values = [];

        foreach ($pairs as $pair) {
            if (is_array($pair)) {
                $key = array_key_first($pair);
                $value = $pair[$key];

                if(is_string($key) && is_array($value)) {
                    $keys[] = $key;
                    $values[] = $value;
                }

                unset($pair);
            }
        }

        $filteredKeys = array_values(array_filter($pairs, fn($item) => $item instanceof IdentifierInterface));
        $filteredValues = array_values(array_filter($pairs, fn($item) => ! $item instanceof IdentifierInterface));
        // Having more values than keys means storing too much information.
        if (count($filteredKeys) < count($filteredValues)) {
            $filteredValues = array_slice($filteredValues, 0, count($filteredKeys));
        }
        // An identifier can not have an array as value.
        $filteredArrayValues = array_keys(array_filter($filteredValues, fn($item) => is_array($item)));

        if(count($filteredArrayValues) > 0) {
            foreach ($filteredArrayValues as $key) {
                unset($filteredValues[$key]);
                unset($filteredKeys[$key]);
            }
        }

        $keys = array_values(array_merge($keys, $filteredKeys));
        $values = array_values(array_merge($values, $filteredValues));

        $this->keys = $keys;
        $this->values = $values;
    }

    public function keyExists(IdentifierInterface $check) : bool
    {
        return in_array($check, $this->keys);
    }

    public function getValue(IdentifierInterface|string $check) : string|int|float|array|null
    {
        $valueKey = array_search($check, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : null;
    }
}