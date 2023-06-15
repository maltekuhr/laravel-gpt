<?php

namespace MalteKuhr\LaravelGPT\Commands\Make;

use MalteKuhr\LaravelGPT\Commands\GPTMakeCommand;

class GPTRequestMakeCommand extends GPTMakeCommand
{
    protected $signature = 'make:gpt-request {name}';

    protected $description = 'Create a new GPT request class';

    protected function getDefaultNamespace(string $name): string
    {
        return "GPT\\Requests\\$name";
    }

    protected function getClassName(): string
    {
        return "GPTRequest";
    }
}