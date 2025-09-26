<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class AcceptedRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'boolean|accepted',
                TestSchema::make()->set('type', 'boolean')->set('description', 'Acceptance is required! Accepted value is true.'),
            ],
            [
                'string|accepted',
                TestSchema::make()->set('type', 'string')->set('description', "Acceptance is required! Accepted values are 'yes', 'on', 1 and true."),
            ],
            [
                ['accepted'],
                TestSchema::make()->set('type', 'string')->set('description', "Acceptance is required! Accepted values are 'yes', 'on', 1 and true.")
            ],
        ];
    }
}