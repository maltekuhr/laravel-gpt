<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
class NullableRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return -100;
    }

    public function handle(): void
    {
        if (in_array('nullable', $this->rules)) {
            if ($this->schemaType === SchemaType::OPEN_API) {
                $this->setField('nullable', true, override: true);
            } else {
                if (!is_null($enum = $this->getField('enum'))) {
                    $this->setField('enum', [...$enum, null], override: true);
                }

         
                $this->setType('null', union: true);
            } 
        }
    }
}