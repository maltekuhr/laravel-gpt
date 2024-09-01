<?php

namespace MalteKuhr\LaravelGpt\Managers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use Exception;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\GptChat;
use MalteKuhr\LaravelGpt\GptFunction;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use ReflectionClass;

class FunctionManager
{
    /**
     * Generates the documentation for the function.
     *
     * @param GptFunction $function
     * @param SchemaType $schemaType
     * @return array
     */
    public function docs(GptFunction $function, SchemaType $schemaType): array
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
     * @param GptFunction $function
     * @param SchemaType $schemaType
     * @return array
     */
    protected function generateSchema(GptFunction $function, SchemaType $schemaType): array
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
     * @param BaseChat $chat
     * @param ChatFunctionCall $functionCall
     * @return ChatFunctionCall
     */
    public function call(BaseChat $chat, ChatFunctionCall $functionCall): ChatFunctionCall
    {
        $function = Arr::first($chat->functions() ?? [], function(GptFunction $function) use ($functionCall) {
            return $function->name() === $functionCall->name;
        });

        if (!$function) {
            throw new Exception('Function not found');
        }

        try {
            $validatedArguments = $this->validate($function, $functionCall->arguments);

            $arguments = $this->getFilteredArguments($function, $validatedArguments);

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
     * Validates the given input against the rules of the given GptFunction.
     *
     * @param GptFunction $function
     * @param array $input
     * @return array
     */
    protected function validate(GptFunction $function, array $input): array
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
     * @param GptFunction $function
     * @param array $arguments
     * @return array
     */
    protected function getFilteredArguments(GptFunction $function, array $arguments): array
    {
        $parameters = (new ReflectionClosure($function->function()))->getParameters();
        return Arr::only($arguments, Arr::pluck($parameters, 'name'));
    }

    /**
     * Extracts the function name from the class name for a given GptFunction.
     *
     * @param GptFunction|GptAction $function
     * @param array $parts
     * @return string
     */
    public function getFunctionName(GptFunction|GptAction $function, array $parts = ['Gpt', 'Function']): string
    {
        $name = (new ReflectionClass(get_class($function)))->getShortName();

        foreach (array_reverse($parts) as $part) {
            $name = str_ends_with($name, $part) ? substr($name, 0, -strlen($part)) : $name;
        }

        return Str::snake($name);
    }
}
