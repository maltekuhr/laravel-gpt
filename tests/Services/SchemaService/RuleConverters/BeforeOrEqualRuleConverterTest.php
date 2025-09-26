<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\InvalidFormatException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class BeforeOrEqualRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'before_or_equal:2021-01-01',
                 TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before or equal to 2021-01-01.'),
            ],
            [
                 'before_or_equal:some_bullshit',
                 InvalidFormatException::class
            ],
            [
                 'before_or_equal:today',
                 TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before or equal to ' . date('Y-m-d') . '.'),
            ],
            [
                 'integer|before_or_equal:today',
                 FieldSetException::class
            ],
        ];
    }
}