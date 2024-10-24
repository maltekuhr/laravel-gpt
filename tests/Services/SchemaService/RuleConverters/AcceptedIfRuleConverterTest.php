<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class AcceptedIfRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'accepted_if:foo,bar',
                TestSchema::make()->set('type', 'string')->set('description', "Acceptance ('yes', 'on', 1 and true) is required if foo is bar.")
            ],
            [
                'accepted_if:foo,bar|boolean',
                TestSchema::make()->set('type', 'boolean')->set('description', "Acceptance (true) is required if foo is bar.")
            ],
            [
                'accepted_if:foo,bar|integer',
                TestSchema::make()->set('type', 'integer')->set('description', "Acceptance (1) is required if foo is bar.")
            ]
        ];
    }
}