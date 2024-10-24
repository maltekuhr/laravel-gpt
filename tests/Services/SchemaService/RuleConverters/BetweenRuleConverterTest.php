<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
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
                 'string|between:1,10',
                 TestSchema::make()->set('type', 'string')->set('description', 'The string must be between 1 and 10 characters long.'),
            ],
            [
                 'integer|between:1,10',
                 TestSchema::make()->set('type', 'integer')->set('description', 'The number must be between 1 and 10.'),
            ],
            [
                 'array|between:1,10',
                 TestSchema::make()->set('type', 'array')->set('description', 'The array must have between 1 and 10 items.'),
            ],
            [
                 ['boolean', 'between:1,10'],
                 IncompatibleValidationRuleException::class,
            ]
        ];
    }

    public function test_if_between_rule_supports_arrays()
    {
        $result = SchemaService::convert([
            'ratings' => 'array|between:1,50',
            'ratings.*' => 'integer'
        ], SchemaType::JSON);

        $expected = [
            'type' => 'object',
            'properties' => [
                'ratings' => [
                    'type' => 'array',
                    'description' => 'The array must have between 1 and 50 items.',
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