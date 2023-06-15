<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Illuminate\Validation\Rules\In;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class InRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'in:')) {
                $parts = explode(',', preg_replace('/^in:/', '', $rule));
            } else if ($rule instanceof In) {
                $parts = explode(',', substr(str_replace('"', '', $rule->__toString()), 3));
            }

            if (isset($parts)) {
                $this->setType('string');
                $this->setField('enum', $parts);
            }
        }
    }
}