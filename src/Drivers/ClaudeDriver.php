<?php

namespace MalteKuhr\LaravelGPT\Drivers;

use Closure;
use MalteKuhr\LaravelGPT\Contracts\Driver;
use MalteKuhr\LaravelGPT\GPTChat;

class ClaudeDriver implements Driver
{
    public function __construct(string $connection) {}

    public function run(GPTChat $chat, bool $lastRotation = false, ?Closure $streamChat = null): void
    {
        // TODO: Implement run() method.
    }
}