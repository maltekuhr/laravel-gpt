<?php

namespace MalteKuhr\LaravelGPT\Extensions;

use Closure;
use MalteKuhr\LaravelGPT\GPTChat;

class FillableGPTChat extends GPTChat
{
    public function __construct(
        protected Closure $systemMessage,
        protected FillableGPTFunction $function,
        protected Closure $model,
        protected Closure $temperature,
        protected Closure $maxTokens,
        protected Closure $sending,
        protected Closure $received
    ) {}

    public function systemMessage(): ?string
    {
        return ($this->systemMessage)();
    }

    public function functions(): array
    {
        return [
            $this->function
        ];
    }

    public function functionCall(): string|bool|null
    {
        return FillableGPTFunction::class;
    }

    public function model(): string
    {
        return ($this->model)();
    }

    public function temperature(): ?float
    {
        return ($this->temperature)();
    }

    public function maxTokens(): ?int
    {
        return ($this->maxTokens)();
    }

    public function sending(): bool
    {
        return ($this->sending)();
    }

    public function received(): bool
    {
        return ($this->received)();
    }
}