<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries;

use Exception;
use Throwable;

class JSONException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = $message . json_last_error_msg();
    }
}