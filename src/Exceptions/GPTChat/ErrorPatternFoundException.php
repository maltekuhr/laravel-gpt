<?php

namespace MalteKuhr\LaravelGPT\Exceptions\GPTChat;

use Exception;
use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use Throwable;

class ErrorPatternFoundException extends Exception
{
    public function __construct(string $message, protected array $messages, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(array $messages): self
    {
        return new self(
            message: 'The model caused the same error twice in a row. Please try to improve your documentation or prompt. If the model still performs poorly try to switch on an more capable model or reduce the complexity of your task!',
            messages: $messages
        );
    }

    public function context(): array
    {
        /*
         * Explanation:
         * We are not providing the entire message history because it could be
         * this could expose sensitive information. Instead, we are providing the
         * function calls and the error messages. As they should not contain
         * sensitive information. There is no package support for rules like
         * password to prevent developers from accidentally exposing sensitive
         * information.
         */
        return [
            'function_calls' => array_map(
                callback: fn (ChatMessage $message) => $message->functionCall->toArray(),
                array: Arr::where($this->messages, function (ChatMessage $message) {
                    return $message->functionCall != null;
                })
            ),
            'errors' => Arr::last($this->messages)->content['errors']
        ];
    }
}
