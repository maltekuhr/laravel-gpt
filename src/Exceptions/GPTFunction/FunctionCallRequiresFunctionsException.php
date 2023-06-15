<?php

namespace MalteKuhr\LaravelGPT\Exceptions\GPTFunction;

use Exception;

class FunctionCallRequiresFunctionsException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'You can not call a function without having any functions defined. Please return null or false!'
        );
    }
}
