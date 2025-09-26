<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

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
                    $this->addDescription("The array must not have more than $value items.");
                } else if ($this->getType() == 'string') {
                    $this->addDescription("The string must not be longer than $value characters.");
                } else if ($this->getType() == 'integer' | $this->getType() == 'number') {
                    $this->addDescription("The number must not be greater than $value.");
                } else {
                    throw new IncompatibleValidationRuleException($rule);
                }
            }
        }
    }
}