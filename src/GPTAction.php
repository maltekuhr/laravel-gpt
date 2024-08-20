<?php

namespace MalteKuhr\LaravelGPT;
use Closure;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;

abstract class GPTAction extends GPTChat
{
    /**
     * The function to be invoked by the model.
     *
     * @return Closure
     */
    abstract public function function(): Closure;

    /**
     * The name of the function to be invoked by the model.
     *
     * @return string
     */
    public function functionName(): string
    {
        return FunctionManager::getFunctionName($this, ['GPT', 'Action']);
    }

    /**
     * Get the description of what the function does.
     *
     * @return string
     */
    public function description(): string
    {
        return 'The function you need to call.';
    }

    /**
     * Get the rules for the function.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Define the functions available for this action.
     *
     * @return array
     */
    public function functions(): array
    {
        return [
            new class($this) extends GPTFunction {
                public function __construct(
                    private GPTAction $action
                ) {}

                public function name(): string
                {
                    return $this->action->functionName();
                }

                public function description(): string
                {
                    return $this->action->description();
                }

                public function function(): Closure
                {
                    return $this->action->function();
                }

                public function rules(): array
                {
                    return $this->action->rules();
                }
            }
        ];
    }

    /**
     * Require the model to call a function.
     *
     * @return bool
     */
    public function functionCall(): bool
    {
        return true;
    }
}