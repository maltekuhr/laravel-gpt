<?php

namespace MalteKuhr\LaravelGPT\Models;

use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallDecodingException;
use OpenAI\Responses\Chat\CreateResponseFunctionCall;

class ChatFunctionCall
{
    /**
     * @param string $name
     * @param array $arguments
     */
    public function __construct(
        public readonly string $name,
        public readonly array $arguments
    ) {}

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return ChatFunctionCall
     */
    public static function from(string $name, array $arguments): ChatFunctionCall
    {
        return new self($name, $arguments);
    }

    /**
     * @param CreateResponseFunctionCall $functionCall
     * @return ChatFunctionCall
     * @throws FunctionCallDecodingException
     */
    public static function fromResponseFunctionCall(CreateResponseFunctionCall $functionCall): ChatFunctionCall
    {
        $arguments = json_decode($functionCall->arguments, true);

        if ($arguments === null) {
            throw FunctionCallDecodingException::create();
        }

        return self::from(
            $functionCall->name,
            $arguments
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => json_encode($this->arguments),
        ];
    }
}