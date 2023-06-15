<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class MaxRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'max:')) {
                preg_match('/^max:(.*)/', $rule, $matches);
                $value = intval($matches[1] ?? 1);

                if ($this->getType() == 'array') {
                    $this->setField('maxItems', $value);
                } else if ($this->getType() == 'string') {
                    $this->setField('maxLength', $value);
                } else if ($this->getType() == 'integer') {
                    $this->setField('maximum', $value);
                } else {
                    if ($this->getType()) {
                        throw new IncompatibleValidationRuleException("The rule 'max' is not compatible with the type '{$this->getType()}'.");
                    } else {
                        throw new IncompatibleValidationRuleException("The rule 'max' requires that a type is defined'.");
                    }
                }
            }
        }
    }
}