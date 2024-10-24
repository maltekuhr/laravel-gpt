<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
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
                 'string|min:1',
                 TestSchema::make()->set('type', 'string')->set('description', 'The string must be at least 1 characters long.'),
            ],
            [
                 'integer|min:1',
                 TestSchema::make()->set('type', 'integer')->set('description', 'The number must be at least 1.'),
            ],
            [
                 'array|min:2',
                 TestSchema::make()->set('type', 'array')->set('description', 'The array must have at least 2 items.'),
            ],
            [
                 ['boolean', 'min:1'],
                 IncompatibleValidationRuleException::class,
            ]
        ];
    }

    public function test_if_min_rule_supports_arrays()
    {
        $result = SchemaService::convert([
            'ratings' => 'array|min:1',
            'ratings.*' => 'integer'
        ], SchemaType::JSON);

        $expected = [
            'type' => 'object',
            'properties' => [
                'ratings' => [
                    'type' => 'array',
                    'description' => 'The array must have at least 1 items.',
                    'items' => [
                        'type' => 'integer'
                    ],
                ],
            ],
            'required' => ['ratings'],
            'additionalProperties' => false,
        ];

        $this->assertEquals($expected, $result);
    }
}