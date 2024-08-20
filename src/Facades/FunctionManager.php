<?php

namespace MalteKuhr\LaravelGPT\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGPT\Enums\SchemaType;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\GPTAction;
use MalteKuhr\LaravelGPT\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Managers\FunctionManager as FunctionManagerClass;

/**
 * @method static array docs(GPTFunction $function, SchemaType $schemaType)
 * @method static ChatFunctionCall call(GPTChat $chat, ChatFunctionCall $functionCall)
 * @method static string getFunctionName(GPTFunction|GPTAction $function, array $parts = ['GPT', 'Function'])
 *
 * @see FunctionManagerClass
 */
class FunctionManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return FunctionManagerClass::class;
    }
}