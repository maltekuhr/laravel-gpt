<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class AlphaRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && preg_match('/^alpha(:|$)/', $rule)) {
                preg_match('/^alpha:(.*)/', $rule, $matches);
                $option = $matches[1] ?? null;

                $this->setType('string');

                if ($option == 'ascii') {
                    $this->setField('pattern', "^[a-zA-Z]+$");
                } else if ($option === null) {
                    $this->setField('pattern', '^[\p{L}\p{M}]+$');
                } else {
                    throw new UnknownValidationOptionException("Unknown validation option '{$option}' for rule 'alpha'.");
                }
            }
        }
    }
}