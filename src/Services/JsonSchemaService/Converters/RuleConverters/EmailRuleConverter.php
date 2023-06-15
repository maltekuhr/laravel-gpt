<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class EmailRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('email', $this->rules)) {
            $this->setType('string');
            $this->setField('format', 'email');
        }
    }
}