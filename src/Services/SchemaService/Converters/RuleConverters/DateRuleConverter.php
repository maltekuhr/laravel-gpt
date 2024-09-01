<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Enums\SchemaType;

class DateRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('date', $this->rules)) {
            $this->setType('string');
            if ($this->schemaType === SchemaType::OPEN_API) {
                $this->addDescription('Format: YYYY-MM-DD (2024-01-01).');
            } else {
                $this->setField('format', 'date');
            }
        }
    }
}