<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\AbstractRuleConverter;

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
                    $this->addDescription("The array must have at least $value items.");
                } else if ($this->getType() == 'string') {
                    $this->addDescription("The string must be at least $value characters long.");
                } else if ($this->getType() == 'integer' | $this->getType() == 'number') {
                    $this->addDescription("The number must be at least $value.");
                }
            }
        }
    }
}