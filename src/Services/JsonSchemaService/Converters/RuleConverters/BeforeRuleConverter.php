<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Carbon\Carbon;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class BeforeRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'before:')) {
                $date = Carbon::parse(
                    explode(',', preg_replace('/^before:/', '', $rule))[0]
                );

                $this->setType('string');
                $this->setField('format', 'date');
                $this->addDescription("Must be a date before {$date->format('Y-m-d')}.");
            }
        }
    }
}