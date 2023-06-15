<?php

namespace MalteKuhr\LaravelGPT\Exceptions\GPTFunction;

use Exception;

class MissingFunctionException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public static function create(string $functionClass, string $requestClass): self
    {
        $functionClass = basename(str_replace('\\', '/', $functionClass));
        $requestClass = basename(str_replace('\\', '/', $requestClass));

        return new self(
            "Could not locate the an instance of ${$functionClass} in the array returned by the 'functions' method of the '${$requestClass}' class."
        );
    }
}
