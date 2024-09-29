<?php

namespace MalteKuhr\LaravelGpt\Commands\Make;

use MalteKuhr\LaravelGpt\Commands\GptMakeCommand;

class GptActionMakeCommand extends GptMakeCommand
{
    protected $signature = 'make:gpt-action {name}';

    protected $description = 'Create a new Gpt action class';

    protected function getDefaultNamespace(string $name): string
    {
        return "Gpt\\Actions\\$name";
    }

    protected function getClassName(): string
    {
        return "GptAction";
    }
}