<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class BooleanRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'boolean',
                'result' => TestSchema::make()->set('type', 'boolean'),
            ],
            [
                'rules' => 'bool',
                'result' => TestSchema::make()->set('type', 'boolean'),
            ]
        ];
    }
}