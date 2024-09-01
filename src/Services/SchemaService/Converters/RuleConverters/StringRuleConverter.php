<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class StringRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        if (in_array('string', $this->rules)) {
            $this->setType('string');
        }
    }
}