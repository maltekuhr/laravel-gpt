<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class AlphaNumRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'alpha_num',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only letters and numbers are allowed.'),
            ],
            [
                 'alpha_num:ascii',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only ASCII letters and numbers are allowed.'),
            ],
            [
                 'alpha_num:test',
                 UnknownValidationOptionException::class
            ],
        ];
    }
}