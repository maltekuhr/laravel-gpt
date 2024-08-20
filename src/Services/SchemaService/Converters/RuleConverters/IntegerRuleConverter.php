<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\AbstractRuleConverter;

class IntegerRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        if (in_array('integer', $this->rules)) {
            $this->setType('integer');
        }
    }
}

