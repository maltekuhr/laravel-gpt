<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
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
                'rules' => 'accepted_if:foo,bar',
                'result' => TestSchema::make()->set('type', 'string')->set('description', "Acceptance ('yes', 'on', 1 and true) is required if foo is bar.")
            ],
            [
                'rules' => 'required|accepted_if:foo,bar|boolean',
                'result' => TestSchema::make()->set('type', 'boolean')->set('description', "Acceptance (true) is required if foo is bar.")->required()
            ],
            [
                'rules' => 'required|accepted_if:foo,bar|integer',
                'result' => TestSchema::make()->set('type', 'integer')->set('description', "Acceptance (1) is required if foo is bar.")->required()
            ]
        ];
    }
}