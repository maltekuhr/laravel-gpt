<?php

namespace MalteKuhr\LaravelGPT\Extensions;

use Closure;
use MalteKuhr\LaravelGPT\GPTFunction;

class FillableGPTFunction extends GPTFunction
{
    public function __construct(
        protected Closure $name,
        protected Closure $description,
        protected Closure $function,
        protected Closure $rules
    ) {}

    public function name(): string
    {
        return ($this->name)();
    }

    public function description(): string
    {
        return ($this->description)();
    }

    public function function (): Closure
    {
        return ($this->function)();
    }

    public function rules(): array
    {
        return ($this->rules)();
    }
}