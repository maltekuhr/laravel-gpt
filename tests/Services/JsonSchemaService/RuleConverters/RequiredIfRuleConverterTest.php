<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class RequiredIfRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'required_if:foo,bar',
                'result' => TestSchema::make()->set('description', 'Required if foo is bar.'),
            ],
            [
                'rules' => ['required_if:foo,bar', 'required_if:baz,qux'],
                'result' => TestSchema::make()->set('description', 'Required if foo is bar.; Required if baz is qux.'),
            ]
        ];
    }
}