<?php

namespace MalteKuhr\LaravelGPT\Generators;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\GPTRequest;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

class ChatPayloadGenerator
{
    /**
     * Generates the payload for the chat completion endpoint.
     *
     * @param GPTRequest $request
     * @return array
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    public static function generate(GPTRequest $request): array
    {
        return array_filter([
            'model' => $request->model(),
            'messages' => self::getMessages($request),
            'functions' => self::getFunctions($request),
            'function_call' => self::getFunctionCall($request),
            'temperature' => $request->temperature(),
            'max_tokens' => $request->maxTokens(),
        ], fn ($value) => $value !== null);
    }

    /**
     * @param GPTRequest $request
     * @return array
     */
    protected static function getMessages(GPTRequest $request): array
    {
        $messages = Arr::map($request->messages, function (ChatMessage $message) {
            return $message->toArray();
        });

        if ($request->systemMessage()) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $request->systemMessage()
            ]);
        }

        return $messages;
    }

    /**
     * @param GPTRequest $request
     * @return array|null
     */
    protected static function getFunctions(GPTRequest $request): ?array
    {
        // handle if function call is null or []
        if ($request->functions() == null) {
            return null;
        }

        // generate docs for functions
        return Arr::map($request->functions(), function (GPTFunction $function): array {
            return FunctionManager::docs($function);
        });
    }

    /**
     * @param GPTRequest $request
     * @return string|array|null
     * @throws FunctionCallRequiresFunctionsException
     * @throws MissingFunctionException
     */
    protected static function getFunctionCall(GPTRequest $request): string|array|null
    {
        // handle if function call is null
        if ($request->functionCall() === null) {
            return null;
        }

        if (is_subclass_of($request->functionCall(), GPTFunction::class)) {
            /* @var GPTFunction $function */
            $function = Arr::first(
                array: $request->functions(),
                callback: fn (GPTFunction $function) => $function instanceof ($request->functionCall())
            );

            // handle if function call is not in functions
            if ($function == null) {
                throw MissingFunctionException::create($request->functionCall(), get_class($request));
            }

            return [
                'name' => $function->name(),
            ];
        }

        if ($request->functionCall() && $request->functions() == null) {
            throw FunctionCallRequiresFunctionsException::create();
        }

        return $request->functionCall() ? 'auto' : 'none';
    }
}