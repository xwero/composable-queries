<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

class PlaceholderReplacementCollection extends BaseCollection
{

    public function __construct(PlaceholderReplacement ...$items)
    {
        foreach ($items as $item) {
            $this->collection[] = $item;
        }
    }

    public function getPlaceholders(): array
    {
        $placeholders = [];

        foreach ($this->collection as $item) {
            $placeholders[] = $item->placeholder;
        }

        return $placeholders;
    }

    public function getReplacements(): array
    {
        $replacements = [];

        foreach ($this->collection as $item) {
            $replacements[] = $item->replacement;
        }

        return $replacements;
    }
}