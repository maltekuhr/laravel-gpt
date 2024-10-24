<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class AlphaRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && ($rule === 'alpha' || str_starts_with($rule, 'alpha:'))) {
                preg_match('/^alpha:(.*)/', $rule, $matches);
                $option = $matches[1] ?? null;

                $this->setType('string');

                if ($option == 'ascii') {
                    $this->addDescription('Only ASCII letters are allowed.');
                } else if ($option === null) {
                    $this->addDescription('Only letters are allowed.');
                } else {
                    throw new UnknownValidationOptionException("Unknown validation option '{$option}' for rule 'alpha'.");
                }
            }
        }
    }
}