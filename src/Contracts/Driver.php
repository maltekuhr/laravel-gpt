<?php

namespace MalteKuhr\LaravelGPT\Contracts;

use MalteKuhr\LaravelGPT\GPTChat;
use Closure;

interface Driver
{
    /**
     * Constructor for the driver.
     *
     * @param string $connection
     */
    public function __construct(string $connection);

    /**
     * Run the AI model with the given chat.
     *
     * @param GPTChat $chat
     * @param Closure|null $streamChat
     * 
     * @return void
     */
    public function run(GPTChat $chat, ?Closure $streamChat = null): void;
}
