<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

;

class BooleanRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 10;
    }

    public function handle(): void
    {
        if (in_array('bool', $this->rules) || in_array('boolean', $this->rules)) {
            $this->setType('boolean');
        }
    }
}