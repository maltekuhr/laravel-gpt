<?php

namespace MalteKuhr\LaravelGPT;

use MalteKuhr\LaravelGPT\Concerns\HasGPTChat;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\Helper\Dir;
use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use MalteKuhr\LaravelGPT\Facades\ChatManager;
use MalteKuhr\LaravelGPT\Contracts\Chatable;

class GPTChat implements Chatable
{
    use HasGPTChat;
    use Dir;


    /**
     * Get the system message for the assistant.
     * 
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return null;
    }

    /**
     * Get available functions for the assistant.
     * 
     * @return GPTFunction[]|null
     */
    public function functions(): ?array
    {
        return null;
    }

    /**
     * Returns the function call behavior: 
     * - true to call any function
     * - false for no function calls
     * - a string with the function class name (e.g., SentimentGPTFunction::class) for a specific function.
     * - an array of function class names (e.g., [SentimentGPTFunction::class, AnotherGPTFunction::class]) for multiple functions.
     * 
     * @return string[]|string|bool|null
     */
    public function functionCall(): array|string|bool|null
    {
        return null;
    }

    /**
     * Get the temperature for the response. (0 - 2)
     * 
     * @return ?float
     */
    public function temperature(): ?float
    {
        return null;
    }

    /**
     * Get the maximum token limit per request.
     * 
     * @return int|null
     */
    public function maxTokens(): ?int
    {
        return null;
    }

    /**
     * Get the model to be used for the request.
     * 
     * @return string
     */
    public function model(): string
    {
        return config('laravel-gpt.default_model');
    }

    /**
     * Run the chat through the chat manager.
     * 
     * @return void
     */
    public function run(bool $sync = false): mixed
    {
        if ($sync) {
            return ChatManager::send($this);
        } else {
            dispatch(fn () => ChatManager::send($this));
            $this->attributes['status'] = ChatStatus::RUNNING;
        }
    }



}