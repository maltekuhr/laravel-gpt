<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\Converters\Test\RuleConverterTest;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class AcceptedIfRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
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