<?php

namespace MalteKuhr\LaravelGPT\Exceptions\GPTFunction;

use Exception;

class FunctionCallDecodingException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public static function create(): self
    {
        return new self(
            'The function call could not be decoded. Please check the function call and try again!'
        );
    }
}
