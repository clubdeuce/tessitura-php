<?php

namespace Clubdeuce\Tessitura\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when the Tessitura API returns an error response
 * or when an HTTP-level failure occurs during a request.
 */
class ApiException extends RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
