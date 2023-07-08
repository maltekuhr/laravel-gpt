<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use Illuminate\Validation\Rule;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class NotInRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'not_in:foo,bar',
                'result' => TestSchema::make()->set('type', 'string')->set('not.enum', ['foo', 'bar']),
            ],
            [
                'rules' => [Rule::notIn(['foo', 'bar'])],
                'result' => TestSchema::make()->set('type', 'string')->set('not.enum', ['foo', 'bar']),
            ]
        ];
    }
}