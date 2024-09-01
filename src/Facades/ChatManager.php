<?php

namespace MalteKuhr\LaravelGpt\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGpt\Managers\ChatManager as ChatManagerClass;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;

/**
 * @method static BaseChat send(BaseChat $chat, int $rotation = 0, bool $sync = true)
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