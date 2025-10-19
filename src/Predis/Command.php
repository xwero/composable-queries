<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\Predis;

final readonly class Command
{
    public function __construct(public string $name, public array|string|int|null $arguments = null)
    {}
}