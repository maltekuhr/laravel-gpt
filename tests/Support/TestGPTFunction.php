<?php

namespace MalteKuhr\LaravelGpt\Tests\Support;

use Closure;
use MalteKuhr\LaravelGpt\GptFunction;

class TestGptFunction extends GptFunction
{
    public function __construct(
        protected Closure $function,
        protected array $rules,
        protected string $description = 'This is a test function.'
    ) {}

    public function function (): Closure
    {
        return $this->function;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function description(): string
    {
        return $this->description;
    }
}