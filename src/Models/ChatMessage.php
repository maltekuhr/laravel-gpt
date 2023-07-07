<?php

namespace MalteKuhr\LaravelGPT\Models;

use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallDecodingException;
use OpenAI\Responses\Chat\CreateResponseMessage;

class ChatMessage
{
    /**
     * @param ChatRole $role
     * @param string|null $name
     * @param mixed|null $content
     * @param ChatFunctionCall|null $functionCall
     */
    public function __construct(
        public readonly ChatRole $role,
        public readonly ?string $name = null,
        public readonly mixed $content = null,
        public readonly ?ChatFunctionCall $functionCall = null
    ) {}

    /**
     * @param ChatRole $role
     * @param string|array|null $name
     * @param ?string $content
     * @param ?ChatFunctionCall $functionCall
     *
     * @return ChatMessage
     */
    public static function from(ChatRole $role, mixed $content, string|array|null $name = null, ?ChatFunctionCall $functionCall = null): ChatMessage
    {
        return new static($role, $name, $content, $functionCall);
    }

    /**
     * @param CreateResponseMessage $message
     * @return ChatMessage
     * @throws FunctionCallDecodingException
     */
    public static function fromResponseMessage(CreateResponseMessage $message): ChatMessage
    {
        return self::from(
            role: ChatRole::from($message->role),
            content: $message->content,
            functionCall: $message->functionCall ? ChatFunctionCall::fromResponseFunctionCall($message->functionCall) : null
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $message = [
            'role' => $this->role->value,
            'content' => is_string($this->content) ? $this->content : json_encode($this->content),
        ];

        if ($this->name) {
            $message['name'] = $this->name;
        }

        if ($this->functionCall) {
            $message['function_call'] = $this->functionCall->toArray();
        }

        return $message;
    }
}