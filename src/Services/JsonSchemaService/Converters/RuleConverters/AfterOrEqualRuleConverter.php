<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Carbon\Carbon;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AfterOrEqualRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'after_or_equal:')) {
                $date = Carbon::parse(
                    explode(',', preg_replace('/^after_or_equal:/', '', $rule))[0]
                );

                $this->setType('string');
                $this->setField('format', 'date');
                $this->addDescription("Must be a date after or equal to {$date->format('Y-m-d')}.");
            }
        }
    }
}