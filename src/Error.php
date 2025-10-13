<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

final readonly class Error
{
    public function __construct(public \Exception $exception)
    {}
}