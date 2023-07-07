<?php

namespace MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\RuleConverters;

use Illuminate\Validation\Rules\NotIn;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\AbstractRuleConverter;

class NotInRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'not_in:')) {
                $parts = explode(',', preg_replace('/^not_in:/', '', $rule));
            } else if ($rule instanceof NotIn) {
                $parts = explode(',', substr(str_replace('"', '', $rule->__toString()), 7));
            }

            if (isset($parts)) {
                $this->setType('string');
                $this->setField('not.enum', $parts);
            }
        }
    }
}