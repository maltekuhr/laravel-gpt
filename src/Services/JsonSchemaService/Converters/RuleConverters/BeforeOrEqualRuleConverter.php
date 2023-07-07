<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Carbon\Carbon;
use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\InvalidFormatException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

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
                $this->setField('format', 'date');
                $this->addDescription('Must be a date before or equal to ' . date('Y-m-d', $date) . '.');
            }
        }
    }
}