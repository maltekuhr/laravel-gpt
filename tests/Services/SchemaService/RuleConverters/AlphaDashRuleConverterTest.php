<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class AlphaDashRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'alpha_dash',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only letters, numbers, dashes and underscores are allowed.'),
            ],
            [
                 'alpha_dash:ascii',
                 TestSchema::make()->set('type', 'string')->set('description', 'Only ASCII letters, numbers, dashes and underscores are allowed.'),
            ],
            [
                 'alpha_dash:test',
                 UnknownValidationOptionException::class
            ],
        ];
    }
}