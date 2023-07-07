<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Illuminate\Validation\Rules\Enum;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class EnumRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof Enum) {
                $enum = (new \ReflectionClass($rule))->getProperty('type')->getValue($rule);
                $values = array_column($enum::cases(), 'value');

                $this->setType(is_string($values[0]) ? 'string' : 'integer');
                $this->setField('enum', $values);
            }
        }
    }
}