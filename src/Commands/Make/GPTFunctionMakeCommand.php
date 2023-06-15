<?php

namespace MalteKuhr\LaravelGPT\Commands\Make;

use MalteKuhr\LaravelGPT\Commands\GPTMakeCommand;

class GPTFunctionMakeCommand extends GPTMakeCommand
{
    protected $signature = 'make:gpt-function {name}';

    protected $description = 'Create a new GPT function class';

    protected function getDefaultNamespace(string $name): string
    {
        return "GPT\\Functions";
    }

    protected function getClassName(): string
    {
        return "GPTFunction";
    }
}