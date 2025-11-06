<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\MongoDb;

use Xwero\ComposableQueries\IdentifierInterface;

final readonly class DocumentBranch
{
    public function __construct(
        public IdentifierInterface $id,
        public mixed $value,
        public string|int|array|null $parents = null,
    )
    {}
}