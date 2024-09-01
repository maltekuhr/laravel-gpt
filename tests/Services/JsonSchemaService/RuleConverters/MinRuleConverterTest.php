<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\JsonSchemaService;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class MinRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'string|min:1',
                'result' => TestSchema::make()->set('type', 'string')->set('minLength', 1),
            ],
            [
                'rules' => 'integer|min:1',
                'result' => TestSchema::make()->set('type', 'integer')->set('minimum', 1),
            ],
            [
                'rules' => ['boolean', 'min:1'],
                'result' => IncompatibleValidationRuleException::class,
            ]
        ];
    }

    public function test_if_between_rule_supports_arrays()
    {
        $result = JsonSchemaService::convert([
            'ratings' => 'array|min:1',
            'ratings.*' => 'integer'
        ]);

        $expected = [
            'type' => 'object',
            'properties' => [
                'ratings' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'integer'
                    ],
                ],
            ],
            'required' => [],
        ];

        $this->assertEquals($expected, $result);
    }
}