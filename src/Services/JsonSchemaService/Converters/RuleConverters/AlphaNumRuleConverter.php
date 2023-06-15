<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

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
                    $this->setField('pattern', "^[a-zA-Z0-9]+$");
                } else if ($option === null) {
                    $this->setField('pattern', '^[\p{L}\p{M}0-9]+$');
                } else {
                    throw new UnknownValidationOptionException("Unknown validation option '{$option}' for rule 'alpha_num'.");
                }
            }
        }
    }
}