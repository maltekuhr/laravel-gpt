<?php

namespace MalteKuhr\LaravelGPT\Commands\Make;

use MalteKuhr\LaravelGPT\Commands\GPTMakeCommand;

class RuleConverterMakeCommand extends GPTMakeCommand
{
    protected $signature = 'make:rule-converter {name}';

    protected $description = 'Create a new custom rule converter';

    protected function getDefaultNamespace(string $name): string
    {
        return "GPT\\RuleConverters";
    }

    protected function getClassName(): string
    {
        return "RuleConverter";
    }
}