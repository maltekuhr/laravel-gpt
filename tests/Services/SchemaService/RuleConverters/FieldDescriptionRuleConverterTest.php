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
                 [FieldDescription::set('foo')],
                 TestSchema::make()->set('description', 'foo'),
            ],
            [
                 [FieldDescription::set('foo'), FieldDescription::set('bar')],
                 TestSchema::make()->set('description', 'foo; bar'),
            ]
        ];
    }
}