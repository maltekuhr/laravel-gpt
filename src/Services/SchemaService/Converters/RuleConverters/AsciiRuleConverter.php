<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class AsciiRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('ascii', $this->rules)) {
            $this->setType('string');
            $this->addDescription('Only ASCII characters are allowed.');
        }
    }
}