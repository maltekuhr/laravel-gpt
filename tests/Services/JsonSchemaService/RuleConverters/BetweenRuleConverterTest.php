<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGPT\Services\JsonSchemaService\JsonSchemaService;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class BetweenRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'string|between:1,10',
                'result' => TestSchema::make()->set('type', 'string')->set('minLength', 1)->set('maxLength', 10),
            ],
            [
                'rules' => 'integer|between:1,10',
                'result' => TestSchema::make()->set('type', 'integer')->set('minimum', 1)->set('maximum', 10),
            ],
            [
                'rules' => ['boolean', 'between:1,10'],
                'result' => IncompatibleValidationRuleException::class,
            ]
        ];
    }

    public function test_if_between_rule_supports_arrays()
    {
        $result = JsonSchemaService::convert([
            'ratings' => 'array|between:1,50',
            'ratings.*' => 'integer'
        ]);

        $expected = [
            'type' => 'object',
            'properties' => [
                'ratings' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 50,
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