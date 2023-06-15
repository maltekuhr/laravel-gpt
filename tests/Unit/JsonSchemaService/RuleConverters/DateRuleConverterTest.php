<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class DateRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
    {
        return [
            [
                'rules' => 'date',
                'result' => TestSchema::make()->set('type', 'string')->set('format', 'date'),
            ]
        ];
    }
}