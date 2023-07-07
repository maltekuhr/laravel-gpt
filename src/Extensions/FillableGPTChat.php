<?php

namespace MalteKuhr\LaravelGPT\Extensions;

use Closure;
use MalteKuhr\LaravelGPT\GPTChat;

class FillableGPTChat extends GPTChat
{
    public function __construct(
        protected ?Closure $systemMessage = null,
        protected ?Closure $functions = null,
        protected ?Closure $functionCall = null,
        protected ?Closure $model = null,
        protected ?Closure $temperature = null,
        protected ?Closure $maxTokens = null,
        protected ?Closure $sending = null,
        protected ?Closure $received = null
    ) {}

    public function systemMessage(): ?string
    {
        return $this->systemMessage ? ($this->systemMessage)() : parent::systemMessage();
    }

    public function functions(): ?array
    {
        return $this->functions ? ($this->functions)() : parent::functions();
    }

    public function functionCall(): string|bool|null
    {
        return $this->functionCall ? ($this->functionCall)() : parent::functionCall();
    }

    public function model(): string
    {
        return $this->model ? ($this->model)() : parent::model();
    }

    public function temperature(): ?float
    {
        return $this->temperature ? ($this->temperature)() : parent::temperature();
    }

    public function maxTokens(): ?int
    {
        return $this->maxTokens ? ($this->maxTokens)() : parent::maxTokens();
    }

    public function sending(): bool
    {
        return $this->sending ? ($this->sending)() : parent::sending();
    }

    public function received(): bool
    {
        return $this->received ? ($this->received)() : parent::received();
    }
}