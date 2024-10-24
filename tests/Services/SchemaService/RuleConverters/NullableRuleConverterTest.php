<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class NullableRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 ['string', 'nullable'],
                 TestSchema::make()->set('type', ['string', 'null']),
            ],
            [
                 'integer|nullable',
                 TestSchema::make()->set('type', ['integer', 'null']),
            ],
        ];
    }
}