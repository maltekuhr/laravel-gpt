<?php

namespace MalteKuhr\LaravelGPT\Contracts;

use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use MalteKuhr\LaravelGPT\GPTFunction;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\Models\GPTChat;

interface Chatable
{
    /**
     * Add a message to the chat.
     *
     * @param ChatMessage $message
     * @return self
     */
    public function addMessage(ChatMessage $message): self;

    /**
     * Update an existing message in the chat.
     *
     * @param int $id
     * @param ChatMessage $message
     * @return self
     */
    public function updateMessage(int $id, ChatMessage $message): self;

    /**
     * Get all messages from the chat.
     *
     * @return ChatMessage[]
     */
    public function getMessages(): array;

    /**
     * Get the latest message from the chat.
     * 
     * @return ChatMessage|null
     */
    public function getLatestMessage(): ?ChatMessage;

    /**
     * Run the chat and get the response.
     *
     * @return static
     */
    public function run(): static;

    /**
     * Run the chat asynchronously.
     *
     * @return void
     */
    public function runAsync(): void;

    /**
     * Save the chat and its associated messages.
     *
     * @return void
     */
    public function save(): void;

    /**
     * Find and instantiate a GPTChat instance by ID and optionally class.
     *
     * @param int $id
     * @param string|null $class
     * @return static|null
     */
    public static function find(int $id, ?string $class = null): ?static;

    /**
     * Get the GPTChat instance associated with this chat.
     *
     * @return GPTChat
     */
    public function getChat(): GPTChat;

    /**
     * Get the current status of the chat.
     *
     * @return ChatStatus
     */
    public function getStatus(): ChatStatus;

    /**
     * Set the current status of the chat.
     *
     * @param ChatStatus $status
     * @return self
     */
    public function setStatus(ChatStatus $status): self;

    /**
     * Get the temperature for the response.
     *
     * @return float|null
     */
    public function temperature(): ?float;

    /**
     * Get the maximum token limit per request.
     *
     * @return int|null
     */
    public function maxTokens(): ?int;

    /**
     * Get the model to be used for the request.
     *
     * @return string
     */
    public function model(): string;

    /**
     * Get available functions for the assistant.
     * 
     * @return GPTFunction[]|null
     */
    public function functions(): ?array;

    /**
     * Returns the function call behavior.
     * 
     * @return string[]|string|bool|null
     */
    public function functionCall(): array|string|bool|null;
}
