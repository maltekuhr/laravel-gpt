<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\InvalidFormatException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class BeforeRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'before:2021-01-01',
                 TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before 2021-01-01.'),
            ],
            [
                 'before:some_bullshit',
                 InvalidFormatException::class
            ],
            [
                 'before:today',
                 TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before ' . date('Y-m-d') . '.'),
            ],
            [
                 'integer|before:today',
                 FieldSetException::class
            ],
        ];
    }
}