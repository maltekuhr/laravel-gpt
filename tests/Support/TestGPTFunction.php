<?php

namespace MalteKuhr\LaravelGPT\Tests\Support;

use Closure;
use MalteKuhr\LaravelGPT\GPTFunction;

class TestGPTFunction extends GPTFunction
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