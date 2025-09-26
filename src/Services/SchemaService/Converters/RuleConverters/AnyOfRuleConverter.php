<?php

namespace MalteKuhr\LaravelGpt\Services\SchemaService\Converters\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\Converters\AbstractRuleConverter;
use MalteKuhr\LaravelGpt\Services\SchemaService\CustomRules\AnyOfRule;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
class AnyOfRuleConverter extends AbstractRuleConverter
{
    public static function priority(): int
    {
        return 10;
    }

    public function handle(): void
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof AnyOfRule) {
                $types = $rule->getTypes();

                $schemaList = array_map(function ($type) use ($rule) {
                    return SchemaService::convert([
                        'type' => ['required', 'string', "in:{$type}"],
                        ...$rule->getRules($type),
                    ], $this->schemaType);
                }, $types);


                if ((is_string($this->schema['type']) && strtolower($this->schema['type']) === 'array') || (is_array($this->schema['type']) && in_array('array', array_map('strtolower', $this->schema['type'])))) {
                    $this->schema['items']['anyOf'] = $schemaList;
                } else {
                    $this->schema['anyOf'] = $schemaList;
                }
            }
        }
    }
}