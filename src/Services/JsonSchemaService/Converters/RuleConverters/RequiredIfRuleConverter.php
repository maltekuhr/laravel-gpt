<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class RequiredIfRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'required_if:')) {
                $parts = explode(',', preg_replace('/^required_if:/', '', $rule));
                $this->addDescription("Required if {$parts[0]} is {$parts[1]}.");
            }
        }
    }
}