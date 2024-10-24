<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\CustomRules;

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

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // This rule doesn't actually validate anything, it's just used to add a description to the field
    }
}
