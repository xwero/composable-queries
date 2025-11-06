<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries\Predis;


use Predis\Client;

final readonly class Connection implements PredisConnectionInterface
{

    public function __construct(public Client $client)
    {}
}