<?php

declare(strict_types=1);

namespace MalteKuhr\LaravelGPT\Exceptions;

use InvalidArgumentException;

/**
 * @internal
 */
final class ApiKeyIsMissingException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'The OpenAI API Key is missing. Please publish the [laravel-gpt.php] configuration file and set the [api_key].'
        );
    }
}