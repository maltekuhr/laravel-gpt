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
     * Generates the documentation for the function.
     *
     * @param GPTFunction $function
     * @return array
     */
    public static function docs(GPTFunction $function): array
    {
        $schema = self::generateSchema($function);

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
     * @return array
     */
    protected static function generateSchema(GPTFunction $function): array
    {
        $schema = JsonSchemaService::convert($function->rules());

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
     * @param GPTFunction $function
     * @param array $arguments
     * @return ChatMessage
     */
    public static function call(GPTFunction $function, array $arguments): ChatMessage
    {
        try {
            self::validate($function, $arguments);

            $arguments = self::filterArguments($function, $arguments);

            $content = call_user_func_array(
                callback: $function->function(),
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
            name: $function->name()
        );
    }

    /**
     * Validates the given input against the rules of the given GPTFunction.
     *
     * @param GPTFunction $function
     * @param array $input
     * @return array
     */
    protected static function validate(GPTFunction $function, array $input): array
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
     * Removes all arguments which are not defined in the function.
     *
     * @param GPTFunction $function
     * @param array $arguments
     * @return array
     */
    protected static function filterArguments(GPTFunction $function, array $arguments): array
    {
        $parameters = (new ReflectionClosure($function->function()))->getParameters();
        return Arr::only($arguments, Arr::pluck($parameters, 'name'));
    }

    /**
     * Extracts the function name from the class name for a given GPTFunction.
     *
     * @param GPTFunction $function
     * @return string
     */
    public static function getFunctionName(GPTFunction $function): string
    {
        $name = (new ReflectionClass(get_class($function)))->getShortName();
        $name = str_ends_with($name, 'Function') ? substr($name, 0, -8) : $name;
        $name = str_ends_with($name, 'GPT') ? substr($name, 0, -3) : $name;
        return Str::snake($name);
    }
}
