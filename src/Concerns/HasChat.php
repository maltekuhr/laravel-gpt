<?php

namespace MalteKuhr\LaravelGPT\Concerns;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

trait HasChat
{
    /**
     * @var array<ChatMessage>
     */
    public array $messages = [];

    /**
     * @param ChatMessage|string $message
     * @return static
     */
    public function addMessage(ChatMessage|string $message): static
    {
        if (is_string($message)) {
            $message = ChatMessage::from(
                role: ChatRole::USER,
                content: $message
            );
        }

        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return ChatMessage
     */
    public function latestMessage(): ChatMessage
    {
        return Arr::last($this->messages);
    }
}