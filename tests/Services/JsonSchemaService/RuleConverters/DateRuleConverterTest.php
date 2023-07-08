<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class DateRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'date',
                'result' => TestSchema::make()->set('type', 'string')->set('format', 'date'),
            ]
        ];
    }
}