<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\CustomRules\FieldDescription;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class FieldDescriptionRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => [FieldDescription::set('foo')],
                'result' => TestSchema::make()->set('description', 'foo'),
            ],
            [
                'rules' => [FieldDescription::set('foo'), FieldDescription::set('bar')],
                'result' => TestSchema::make()->set('description', 'foo; bar'),
            ]
        ];
    }
}