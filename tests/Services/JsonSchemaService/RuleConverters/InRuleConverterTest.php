<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use Illuminate\Validation\Rule;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class InRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'in:foo,bar',
                'result' => TestSchema::make()->set('type', 'string')->set('enum', ['foo', 'bar']),
            ],
            [
                'rules' => [Rule::in(['foo', 'bar'])],
                'result' => TestSchema::make()->set('type', 'string')->set('enum', ['foo', 'bar']),
            ]
        ];
    }
}