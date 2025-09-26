<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\InvalidFormatException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class AfterOrEqualRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'after_or_equal:')) {
                $date = strtotime(
                    $value = explode(',', preg_replace('/^after_or_equal:/', '', $rule))[0]
                );

                $this->setType('string');
                $this->addDescription('Format: YYYY-MM-DD (2024-01-01).');


                if ($date === false) {
                    $this->addDescription('Must be a date after or equal to ' . $value . '.');
                } else {
                    $this->addDescription('Must be a date after or equal to ' . date('Y-m-d', $date) . '.');
                }

            }
        }
    }
}