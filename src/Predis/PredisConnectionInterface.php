<?php

namespace Xwero\ComposableQueries\Predis;

use Predis\Client;

interface PredisConnectionInterface
{
    function __construct(Client $client);
}