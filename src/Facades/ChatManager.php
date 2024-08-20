<?php

namespace MalteKuhr\LaravelGPT\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGPT\Managers\ChatManager as ChatManagerClass;
use MalteKuhr\LaravelGPT\GPTChat;

/**
 * @method static GPTChat send(GPTChat $chat)
 *
 * @see ChatManagerClass
 */
class ChatManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ChatManagerClass::class;
    }
}