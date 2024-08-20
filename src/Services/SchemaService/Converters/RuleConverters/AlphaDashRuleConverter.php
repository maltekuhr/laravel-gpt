<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\AbstractRuleConverter;

class AlphaDashRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'alpha_dash')) {
                preg_match('/^alpha_dash:(.*)/', $rule, $matches);
                $option = $matches[1] ?? null;

                $this->setType('string');

                if ($option == 'ascii') {
                    $this->addDescription('Only ASCII letters, numbers, dashes and underscores are allowed.');
                } else if ($option === null) {
                    $this->addDescription('Only letters, numbers, dashes and underscores are allowed.');
                } else {
                    throw new UnknownValidationOptionException("Unknown validation option '{$option}' for rule 'alpha_dash'.");
                }
            }
        }
    }
}