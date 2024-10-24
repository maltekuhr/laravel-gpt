<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use Illuminate\Validation\Rule;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class NotInRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'not_in:foo,bar',
                 TestSchema::make()->set('type', 'string')->set('description', 'Must not be one of foo, bar.'),
            ],
            [
                 [Rule::notIn(['foo', 'bar'])],
                 TestSchema::make()->set('type', 'string')->set('description', 'Must not be one of foo, bar.'),
            ]
        ];
    }
}