<?php

namespace MalteKuhr\LaravelGpt\Commands\Make;

use MalteKuhr\LaravelGpt\Commands\GptMakeCommand;

class GptFunctionMakeCommand extends GptMakeCommand
{
    protected $signature = 'make:gpt-function {name}';

    protected $description = 'Create a new Gpt function class';

    protected function getDefaultNamespace(string $name): string
    {
        return "Gpt\\Functions";
    }

    protected function getClassName(): string
    {
        return "GptFunction";
    }
}