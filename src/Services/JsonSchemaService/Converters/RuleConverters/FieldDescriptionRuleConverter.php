<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\CustomRules\FieldDescription;

;

class FieldDescriptionRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -100;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof FieldDescription) {
                $this->addDescription($rule->description);
            }
        }
    }
}