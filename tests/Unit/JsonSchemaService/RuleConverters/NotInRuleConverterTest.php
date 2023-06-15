<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use Illuminate\Validation\Rule;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class NotInRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
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