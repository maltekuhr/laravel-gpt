<?php

namespace MalteKuhr\LaravelGpt\Exceptions;

use Exception;

class InvalidJsonResponseException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param int $traceId
     * @param string $errorMessage
     * @return self
     */
    public static function create(int $traceId, string $errorMessage): self
    {
        return new self(
            "Invalid JSON response in action (Trace ID: {$traceId}): {$errorMessage}"
        );
    }
}
