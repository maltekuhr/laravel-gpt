<?php

namespace MalteKuhr\LaravelGPT\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatFunctionCall;
use Exception;
use MalteKuhr\LaravelGPT\Enums\SchemaType;
use MalteKuhr\LaravelGPT\GPTAction;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use MalteKuhr\LaravelGPT\Services\SchemaService\SchemaService;
use ReflectionClass;

class FunctionManager
{
    /**
     * Generates the documentation for the function.
     *
     * @param GPTFunction $function
     * @param SchemaType $schemaType
     * @return array
     */
    public function docs(GPTFunction $function, SchemaType $schemaType): array
    {
        $schema = $this->generateSchema($function, $schemaType);

        return [
            'name' => $function->name(),
            'parameters' => collect($schema)->toArray(),
            'description' => $function->description()
        ];
    }

    /**
     * Generates the json schema for the function.
     *
     * @param GPTFunction $function
     * @param SchemaType $schemaType
     * @return array
     */
    protected function generateSchema(GPTFunction $function, SchemaType $schemaType): array
    {
        $schema = SchemaService::convert($function->rules(), $schemaType);

        $reflection = new ReflectionClosure($function->function());
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
     * @param GPTChat $chat
     * @param ChatFunctionCall $functionCall
     * @return ChatFunctionCall
     */
    public function call(GPTChat $chat, ChatFunctionCall $functionCall): ChatFunctionCall
    {
        $function = Arr::first($chat->functions() ?? [], function(GPTFunction $function) use ($functionCall) {
            return $function->name() === $functionCall->name;
        });

        if (!$function) {
            throw new Exception('Function not found');
        }

        try {
            $this->validate($function, $functionCall->arguments);

            $arguments = $this->getFilteredArguments($function, $functionCall->arguments);

            $content = call_user_func_array(
                callback: $function->function(),
                args: $arguments
            );
        } catch (Exception $exception) {
            return $functionCall->handleException($exception);
        }

        return $functionCall->setResponse($content);
    }

    /**
     * Validates the given input against the rules of the given GPTFunction.
     *
     * @param GPTFunction $function
     * @param array $input
     * @return array
     */
    protected function validate(GPTFunction $function, array $input): array
    {
        $validator = Validator::make(
            $input, $function->rules(), $function->messages()
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
     * @param GPTFunction $function
     * @param array $arguments
     * @return array
     */
    protected function getFilteredArguments(GPTFunction $function, array $arguments): array
    {
        $parameters = (new ReflectionClosure($function->function()))->getParameters();
        return Arr::only($arguments, Arr::pluck($parameters, 'name'));
    }

    /**
     * Extracts the function name from the class name for a given GPTFunction.
     *
     * @param GPTFunction|GPTAction $function
     * @param array $parts
     * @return string
     */
    public function getFunctionName(GPTFunction|GPTAction $function, array $parts = ['GPT', 'Function']): string
    {
        $name = (new ReflectionClass(get_class($function)))->getShortName();

        foreach (array_reverse($parts) as $part) {
            $name = str_ends_with($name, $part) ? substr($name, 0, -strlen($part)) : $name;
        }

        return Str::snake($name);
    }
}
