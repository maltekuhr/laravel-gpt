<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Enums\SchemaType;

class EmailRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('email', $this->rules)) {
            $this->setType('string');
            if ($this->schemaType === SchemaType::OPEN_API) {
                $this->addDescription('Format: email@example.com.');
            } else {
                $this->setField('format', 'email');
            }
        }
    }
}