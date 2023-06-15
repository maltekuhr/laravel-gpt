<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class DateRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('date', $this->rules)) {
            $this->setType('string');
            $this->setField('format', 'date');
        }
    }
}