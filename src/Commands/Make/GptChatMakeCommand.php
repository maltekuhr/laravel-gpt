<?php

namespace MalteKuhr\LaravelGpt\Commands\Make;

use MalteKuhr\LaravelGpt\Commands\GptMakeCommand;

class GptChatMakeCommand extends GptMakeCommand
{
    protected $signature = 'make:gpt-chat {name}';

    protected $description = 'Create a new Gpt chat class';

    protected function getDefaultNamespace(string $name): string
    {
        return "Gpt\\Chats\\$name";
    }

    protected function getClassName(): string
    {
        return "GptChat";
    }
}