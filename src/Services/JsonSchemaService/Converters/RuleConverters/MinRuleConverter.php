<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class MinRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'min:')) {
                preg_match('/^min:(.*)/', $rule, $matches);
                $value = intval($matches[1] ?? 1);

                if ($this->getType() == 'array') {
                    $this->setField('minItems', $value);
                } else if ($this->getType() == 'string') {
                    $this->setField('minLength', $value);
                } else if ($this->getType() == 'integer') {
                    $this->setField('minimum', $value);
                } else {
                    if ($this->getType()) {
                        throw new IncompatibleValidationRuleException("The rule 'min' is not compatible with the type '{$this->getType()}'.");
                    } else {
                        throw new IncompatibleValidationRuleException("The rule 'min' requires that a type is defined'.");
                    }
                }
            }
        }
    }
}