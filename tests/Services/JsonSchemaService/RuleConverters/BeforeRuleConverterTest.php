<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\InvalidFormatException;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class BeforeRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'before:2021-01-01',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before 2021-01-01.'),
            ],
            [
                'rules' => 'before:some_bullshit',
                'result' => InvalidFormatException::class
            ],
            [
                'rules' => 'before:today',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before ' . date('Y-m-d') . '.'),
            ],
            [
                'rules' => 'integer|before:today',
                'result' => FieldSetException::class
            ],
        ];
    }
}