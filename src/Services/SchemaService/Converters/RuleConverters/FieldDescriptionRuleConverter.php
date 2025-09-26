<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\CustomRules\FieldDescription;

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