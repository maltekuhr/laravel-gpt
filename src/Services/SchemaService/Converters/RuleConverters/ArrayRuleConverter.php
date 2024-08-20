<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\AbstractRuleConverter;

class ArrayRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('array', $this->rules)) {
            $this->setType('array');
        }
    }
}