<?php

namespace MalteKuhr\LaravelGpt\Commands\Make;

use MalteKuhr\LaravelGpt\Commands\GptMakeCommand;

class RuleConverterMakeCommand extends GptMakeCommand
{
    protected $signature = 'make:rule-converter {name}';

    protected $description = 'Create a new custom rule converter';

    protected function getDefaultNamespace(string $name): string
    {
        return "Gpt\\RuleConverters";
    }

    protected function getClassName(): string
    {
        return "RuleConverter";
    }
}