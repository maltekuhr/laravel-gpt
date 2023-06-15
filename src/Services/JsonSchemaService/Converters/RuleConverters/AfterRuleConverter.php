<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Carbon\Carbon;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AfterRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'after:')) {
                $date = Carbon::parse(
                    explode(',', preg_replace('/^after:/', '', $rule))[0]
                );

                $this->setType('string');
                $this->setField('format', 'date');
                $this->addDescription("Must be a date after {$date->format('Y-m-d')}.");
            }
        }
    }
}