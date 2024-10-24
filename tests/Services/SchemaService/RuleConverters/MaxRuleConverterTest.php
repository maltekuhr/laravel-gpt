<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Exceptions\SchemaService\IncompatibleValidationRuleException;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class MaxRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                 'string|max:10',
                 TestSchema::make()->set('type', 'string')->set('description', 'The string must not be longer than 10 characters.'),
            ],
            [
                 'integer|max:10',
                 TestSchema::make()->set('type', 'integer')->set('description', 'The number must not be greater than 10.'),
            ],
            [
                 'array|max:5',
                 TestSchema::make()->set('type', 'array')->set('description', 'The array must not have more than 5 items.'),
            ],
            [
                 ['boolean', 'max:1'],
                 IncompatibleValidationRuleException::class,
            ]
        ];
    }

    public function test_if_max_rule_supports_arrays()
    {
        $result = SchemaService::convert([
            'ratings' => 'array|max:3',
            'ratings.*' => 'integer'
        ], SchemaType::JSON);

        $expected = [
            'type' => 'object',
            'properties' => [
                'ratings' => [
                    'type' => 'array',
                    'description' => 'The array must not have more than 3 items.',
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