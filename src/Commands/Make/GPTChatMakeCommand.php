<?php

namespace MalteKuhr\LaravelGPT\Commands\Make;

use MalteKuhr\LaravelGPT\Commands\GPTMakeCommand;

class GPTChatMakeCommand extends GPTMakeCommand
{
    protected $signature = 'make:gpt-chat {name}';

    protected $description = 'Create a new GPT chat class';

    protected function getDefaultNamespace(string $name): string
    {
        return "GPT\\Chats\\$name";
    }

    protected function getClassName(): string
    {
        return "GPTChat";
    }
}