<?php

namespace MalteKuhr\LaravelGPT\Extensions;

use Closure;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\GPTAction;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

class FillableGPTAction extends GPTAction
{
    public function __construct(
        protected Closure $systemMessage,
        protected Closure $function,
        protected Closure $rules,
        protected ?Closure $functionName = null,
        protected ?Closure $model = null,
        protected ?Closure $temperature = null,
        protected ?Closure $maxTokens = null,
        protected ?Closure $sending = null,
        protected ?Closure $received = null
    ) {}

    public function systemMessage(): ?string
    {
        return ($this->systemMessage)();
    }

    public function function(): Closure
    {
        return $this->function;
    }

    public function rules(): array
    {
        return ($this->rules)();
    }

    public function functionName(): string
    {
        return $this->functionName ? ($this->functionName)() : parent::functionName();
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