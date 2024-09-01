<?php

namespace MalteKuhr\LaravelGpt\Exceptions\GptChat;

use Exception;

class NoFunctionCallException extends Exception
{
    public static function modelMessage(): string
    {
        return 'A function call against this method was required. The function call is missing.';
    }

    public static function create(): static
    {
        return new static('The function call is missing.');
    }
}
