<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class RequiredIfRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
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