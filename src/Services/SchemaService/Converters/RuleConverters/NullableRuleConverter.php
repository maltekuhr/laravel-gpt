<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class NullableRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -100;
    }

    public function handle(): void
    {
        if (in_array('nullable', $this->rules)) {
            $this->setType('null', union: true);
        }
    }
}