<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class BetweenRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'between:')) {
                $parts = array_map(function ($part) {
                    return intval($part);
                }, explode(',', preg_replace('/^between:/', '', $rule)));

                if ($this->getType() == 'array') {
                    $this->setField('minItems', $parts[0]);
                    $this->setField('maxItems', $parts[1]);
                } else if ($this->getType() == 'string') {
                    $this->setField('minLength', $parts[0]);
                    $this->setField('maxLength', $parts[1]);
                } else if ($this->getType() == 'integer') {
                    $this->setField('minimum', $parts[0]);
                    $this->setField('maximum', $parts[1]);
                } else {
                    throw new IncompatibleValidationRuleException("The rule 'between' is not compatible with the type '{$this->getType()}'.");
                }
            }
        }
    }
}