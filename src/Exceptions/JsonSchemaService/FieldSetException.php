<?php

declare(strict_types=1);

namespace MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService;

use InvalidArgumentException;

/**
 * @internal
 */
final class FieldSetException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public static function create(string $field): self
    {
        return new self(
            "'The field $field is already set. Make sure to remove conflicting rules.'"
        );
    }
}