<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

class DecimalRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        if (in_array('decimal', $this->rules)) {
            $this->setType('number');
        }
    }
}