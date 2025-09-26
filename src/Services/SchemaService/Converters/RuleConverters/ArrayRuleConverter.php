<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class ArrayRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('array', $this->rules) && is_null($this->getField('anyOf'))) {
            $this->setType('array');
        }
    }
}