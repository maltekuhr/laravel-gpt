<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

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