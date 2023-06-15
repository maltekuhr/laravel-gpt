<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AcceptedRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('accepted', $this->rules)) {
            if ($this->getType() == 'boolean') {
               $this->addDescription("Acceptance is required! Accepted value is true.");
            } else {
                $this->setType('string');
                $this->addDescription("Acceptance is required! Accepted values are 'yes', 'on', 1 and true.");
            }
        }
    }
}