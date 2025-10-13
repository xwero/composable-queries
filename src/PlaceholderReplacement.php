<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

final readonly class PlaceholderReplacement
{
    public function __construct(
        public string $placeholder,
        public string $replacement,
    )
    {}
}