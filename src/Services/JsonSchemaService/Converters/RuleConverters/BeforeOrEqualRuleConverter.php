<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Carbon\Carbon;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class BeforeOrEqualRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'before_or_equal:')) {
                $date = Carbon::parse(
                    explode(',', preg_replace('/^before_or_equal:/', '', $rule))[0]
                );

                $this->setType('string');
                $this->setField('format', 'date');
                $this->addDescription("Must be a date before or equal to {$date->format('Y-m-d')}.");
            }
        }
    }
}