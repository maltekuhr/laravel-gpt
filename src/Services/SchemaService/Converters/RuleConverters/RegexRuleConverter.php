<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Enums\SchemaType;

class RegexRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 0;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'regex:')) {
                preg_match('/^regex:(.*)/', $rule, $matches);
                $pattern = $matches[1] ?? '';

                $this->setType('string');
                $this->addDescription('Pattern: ' . $this->convertToJsonSchemaPattern($pattern));
            }
        }
    }

    private function convertToJsonSchemaPattern(string $pattern): string
    {
        // Remove delimiters and flags from the Laravel regex pattern
        $pattern = preg_replace('/^\/|\/$/', '', $pattern);
        
        // Escape forward slashes for JSON Schema
        return str_replace('/', '\/', $pattern);
    }
}