<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use Carbon\Exceptions\InvalidFormatException;
use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class BeforeOrEqualRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
    {
        return [
            [
                'rules' => 'before_or_equal:2021-01-01',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before or equal to 2021-01-01.'),
            ],
            [
                'rules' => 'before_or_equal:some_bullshit',
                'result' => InvalidFormatException::class
            ],
            [
                'rules' => 'before_or_equal:today',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date before or equal to ' . now()->format('Y-m-d') . '.'),
            ],
            [
                'rules' => 'integer|before_or_equal:today',
                'result' => FieldSetException::class
            ],
        ];
    }
}