<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class AlphaRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'alpha',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only letters are allowed.'),
            ],
            [
                 'alpha:ascii',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only ASCII letters are allowed.'),
            ],
            [
                 'alpha:test',
                 UnknownValidationOptionException::class
            ],
        ];
    }
}