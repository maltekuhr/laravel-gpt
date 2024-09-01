<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use Illuminate\Validation\Rules\In;
use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Enums\SchemaType;

class InRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 5;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'in:')) {
                $values = explode(',', preg_replace('/^in:/', '', $rule));
            } elseif ($rule instanceof In) {
                $values = explode(',', substr(str_replace('"', '', $rule->__toString()), 3));
            }

            if (isset($values)) {
                $type = is_string($values[0]) ? 'string' : 'integer';
                $this->setType($type);

                if ($this->schemaType === SchemaType::JSON) {
                    $this->setField('enum', $values);
                } elseif ($type === 'string') {
                    $this->setField('format', 'enum');
                    $this->setField('enum', $values);
                } else {
                    $this->addDescription('Possible values: ' . implode(', ', $values) . '.');
                }
            }
        }
    }
}