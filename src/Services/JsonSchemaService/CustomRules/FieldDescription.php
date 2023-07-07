<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\CustomRules;

use Closure;
use Illuminate\Contracts\Validation\Rule;

class FieldDescription implements Rule
{
    public function __construct(
        public readonly string $description
    ) {}

    public static function set(string $description): static
    {
        return new static($description);
    }

    public function passes($attribute, $value)
    {
        return true;
    }

    public function message()
    {
        return [];
    }
}
