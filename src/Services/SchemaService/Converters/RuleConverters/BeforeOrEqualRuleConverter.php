<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\InvalidFormatException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class BeforeOrEqualRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'before_or_equal:')) {
                $date = strtotime(
                    explode(',', preg_replace('/^before_or_equal:/', '', $rule))[0]
                );

                if ($date === false) {
                    throw new InvalidFormatException(
                        "Invalid date format in rule '$rule'."
                    );
                }

                $this->setType('string');

                if ($this->schemaType === SchemaType::OPEN_API) {
                    $this->addDescription('Format: YYYY-MM-DD (2024-01-01).');
                } else {
                    $this->setField('format', 'date');
                }

                $this->addDescription('Must be a date before or equal to ' . date('Y-m-d', $date) . '.');
            }
        }
    }
}