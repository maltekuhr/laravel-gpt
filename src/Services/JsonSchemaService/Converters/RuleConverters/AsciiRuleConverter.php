<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AsciiRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('ascii', $this->rules)) {
            $this->setType('string');
            $this->setField('pattern', '^[\x00-\x7F]+$');
        }
    }
}