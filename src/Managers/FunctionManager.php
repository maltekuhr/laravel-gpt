<?php

namespace MalteKuhr\LaravelGPT\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\JsonSchemaService;
use ReflectionClass;

class FunctionManager
{
    /**
     * @param GPTFunction $function
     */
    protected function __construct(
        protected GPTFunction $function
    ) {}

    /**
     * @param GPTFunction $function
     * @return self
     */
    public static function make(GPTFunction $function): self
    {
        return new self($function);
    }

    /**
     * Generates the documentation for the function.
     *
     * @return array
     */
    public function docs(): array
    {
        $schema = self::generateSchema();

        return [
            'name' => $this->function->name(),
            'parameters' => collect($schema)->toArray(),
            'description' => $this->function->description()
        ];
    }

    /**
     * Generates the json schema for the function.
     *
     * @return array
     */
    protected function generateSchema(): array
    {
        $schema = JsonSchemaService::convert($this->function->rules());

        $reflection = new ReflectionClosure($this->function->function());
        foreach ($reflection->getParameters() as $parameter) {
            if (!$parameter->isDefaultValueAvailable() && !$parameter->allowsNull()) {
                $schema['required'] = array_unique([...$schema['required'], $parameter->getName()]);
            }
        }

        return $schema;
    }

    /**
     * Calls the function and validates the arguments.
     *
     * @param array $arguments
     * @return ChatMessage
     */
    public function call(array $arguments): ChatMessage
    {
        try {
            self::validate($arguments);

            $arguments = $this->getFilteredArguments($arguments);

            $content = call_user_func_array(
                callback: $this->function->function(),
                args: $arguments
            );
        } catch (ValidationException $exception) {
            $content = [
                'errors' => Arr::flatten($exception->errors())
            ];
        }

        return ChatMessage::from(
            role: ChatRole::FUNCTION,
            content: $content,
            name: $this->function->name()
        );
    }

    /**
     * Validates the given input against the rules of the given GPTFunction.
     *
     * @param array $input
     * @return array
     */
    protected function validate(array $input): array
    {
        $validator = Validator::make(
            $input, $this->function->rules(), $this->function->messages()
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages(
                $validator->errors()->toArray()
            );
        }

        return $validator->validated();
    }

    /**
     * Removes all arguments which can't be passed to the function.
     *
     * @param array $arguments
     * @return array
     */
    protected function getFilteredArguments(array $arguments): array
    {
        $parameters = (new ReflectionClosure($this->function->function()))->getParameters();
        return Arr::only($arguments, Arr::pluck($parameters, 'name'));
    }

    /**
     * Extracts the function name from the class name for a given GPTFunction.
     *
     * @param array $parts
     * @return string
     */
    public static function getFunctionName($function, array $parts = ['GPT', 'Function']): string
    {
        $name = (new ReflectionClass(get_class($function)))->getShortName();

        foreach (array_reverse($parts) as $part) {
            $name = str_ends_with($name, $part) ? substr($name, 0, -strlen($part)) : $name;
        }

        return Str::snake($name);
    }
}
