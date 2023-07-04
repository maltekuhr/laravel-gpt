<?php

namespace MalteKuhr\LaravelGPT\Commands\Make;

use MalteKuhr\LaravelGPT\Commands\GPTMakeCommand;

class GPTActionMakeCommand extends GPTMakeCommand
{
    protected $signature = 'make:gpt-action {name}';

    protected $description = 'Create a new GPT action class';

    protected function getDefaultNamespace(string $name): string
    {
        return "GPT\\Actions\\$name";
    }

    protected function getClassName(): string
    {
        return "GPTAction";
    }
}