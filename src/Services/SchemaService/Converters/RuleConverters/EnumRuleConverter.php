<?php

namespace MalteKuhr\LaravelGPT\Services\SchemaService\Converters\RuleConverters;

use Illuminate\Validation\Rules\Enum;
use MalteKuhr\LaravelGPT\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGPT\Enums\SchemaType;

class EnumRuleConverter extends AbstractRuleConverter
{
    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof Enum) {
                $enum = (new \ReflectionClass($rule))->getProperty('type')->getValue($rule);
                $values = array_column($enum::cases(), 'value');

                $type = is_string($values[0]) ? 'string' : 'integer';
                $this->setType($type);

                if ($this->schemaType === SchemaType::JSON) {
                    $this->setField('enum', $values);

                } else if ($type === 'string') {
                    $this->setField('format', 'enum');
                    $this->setField('enum', $values);
                } else {
                    $this->addDescription('Possible values: ' . implode(', ', $values) . '.');
                }
            }
        }
    }
}