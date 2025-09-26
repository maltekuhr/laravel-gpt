<?php

namespace MalteKuhr\LaravelGpt\Facades;

use Illuminate\Support\Facades\Facade;
use MalteKuhr\LaravelGpt\Managers\ActionManager as ActionManagerClass;
use MalteKuhr\LaravelGpt\GptAction;

/**
 * @method static GptAction send(GptAction $action, int $tries = 1)
 *
 * @see ActionManagerClass
 */
class ActionManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ActionManagerClass::class;
    }
}