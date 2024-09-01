<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;

;

class RequiredRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        if (in_array('required', $this->rules)) {
            if ($this->path == '*') {
                throw new IncompatibleValidationRuleException('The required rule cannot be applied to array items. Use the min and max rules instead.');
            } else {
                $this->schema['required'][] = $this->path;
            }
        }
    }
}