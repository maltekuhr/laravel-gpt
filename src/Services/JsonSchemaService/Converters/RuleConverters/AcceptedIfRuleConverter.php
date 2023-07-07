<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AcceptedIfRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'accepted_if:')) {
                $parts = explode(',', preg_replace('/^accepted_if:/', '', $rule));
                if ($this->getType() == 'boolean') {
                    $this->addDescription("Acceptance (true) is required if {$parts[0]} is {$parts[1]}.");
                } else if ($this->getType() == 'integer') {
                    $this->addDescription("Acceptance (1) is required if {$parts[0]} is {$parts[1]}.");
                } else {
                    $this->setType('string');
                    $this->addDescription("Acceptance ('yes', 'on', 1 and true) is required if {$parts[0]} is {$parts[1]}.");
                }
            }
        }
    }
}