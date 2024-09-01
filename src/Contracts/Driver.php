<?php

namespace MalteKuhr\LaravelGpt\Contracts;

use MalteKuhr\LaravelGpt\Contracts\BaseChat;
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
     * @param BaseChat $chat
     * @param Closure|null $streamChat
     * 
     * @return void
     */
    public function run(BaseChat $chat, ?Closure $streamChat = null): void;
}
