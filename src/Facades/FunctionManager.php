<?php

namespace MalteKuhr\LaravelGpt\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\GptFunction;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Managers\FunctionManager as FunctionManagerClass;

/**
 * @method static array docs(GptFunction $function, SchemaType $schemaType)
 * @method static ChatFunctionCall call(BaseChat $chat, ChatFunctionCall $functionCall)
 * @method static string getFunctionName(GptFunction|GptAction $function, array $parts = ['Gpt', 'Function'])
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