<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

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
                preg_match('/^between:(.*)/', $rule, $matches);
                $parts = array_map(function ($part) {
                    return intval($part);
                }, explode(',', $matches[1] ?? ''));

                if ($this->getType() == 'array') {
                    $this->addDescription("The array must have between {$parts[0]} and {$parts[1]} items.");
                } else if ($this->getType() == 'string') {
                    $this->addDescription("The string must be between {$parts[0]} and {$parts[1]} characters long.");
                } else if ($this->getType() == 'integer') {
                    $this->addDescription("The number must be between {$parts[0]} and {$parts[1]}.");
                } else {
                    throw new IncompatibleValidationRuleException("The rule 'between' is not compatible with the type '{$this->getType()}'.");
                }
            }
        }
    }
}