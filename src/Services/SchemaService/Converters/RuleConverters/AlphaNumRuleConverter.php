<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class AlphaNumRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'alpha_num')) {
                preg_match('/^alpha_num:(.*)/', $rule, $matches);
                $option = $matches[1] ?? null;

                $this->setType('string');

                if ($option == 'ascii') {
                    $this->addDescription('Only ASCII letters and numbers are allowed.');
                } else if ($option === null) {
                    $this->addDescription('Only letters and numbers are allowed.');
                } else {
                    throw new UnknownValidationOptionException("Unknown validation option '{$option}' for rule 'alpha_num'.");
                }
            }
        }
    }
}