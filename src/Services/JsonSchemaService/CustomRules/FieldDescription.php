<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\CustomRules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FieldDescription implements ValidationRule
{
    public function __construct(
        public readonly string $description
    ) {}

    public static function set(string $description): static
    {
        return new static($description);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
