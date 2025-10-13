<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

use BackedEnum;

class QueryParametersCollection extends TypeCollection
{
    public function __construct(ReplacementInterface|string|int|float|array ...$pairs)
    {
        $arrays = [];

        foreach ($pairs as $pair) {
            if (is_array($pair)) {
                $key = array_key_first($pair);
                $value = $pair[$key];

                if(is_string($key) && is_array($value)) {
                    $arrays[$key] = $value;
                }

                unset($pair);
            }
        }

        $keys = array_filter($pairs, fn($item) => $item instanceof ReplacementInterface);
        $values = array_filter($pairs, fn($item) => ! $item instanceof ReplacementInterface);
        // Having more values than keys means storing too much information.
        if (count($keys) < count($values)) {
            $values = array_slice($values, 0, count($keys));
        }

        if(count($arrays) > 0) {
            $keys = array_merge($keys, array_keys($arrays));
            $values = array_merge($values, array_values($arrays));
        }

        $this->keys = array_values($keys);
        $this->values = array_values($values);
    }

    public function getValue(ReplacementInterface $check) : string|int|float|null
    {
        $valueKey = array_search($check, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : null;
    }

    public function getArrayValue(string $check) : array|null
    {
        $valueKey = array_search($check, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : null;
    }
}