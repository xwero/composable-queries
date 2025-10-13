<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

abstract class TypeCollection
{
    protected array $keys = [];
    protected array $values = [];

    public function keyExists(ReplacementInterface $check) : bool
    {
        return in_array($check, $this->keys);
    }
}