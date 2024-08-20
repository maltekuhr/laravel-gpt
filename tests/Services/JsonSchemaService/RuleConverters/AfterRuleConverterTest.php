<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Exceptions\SchemaService\InvalidFormatException;
use MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class AfterRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'after:2021-01-01',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date after 2021-01-01.'),
            ],
            [
                'rules' => 'after:some_bullshit',
                'result' => InvalidFormatException::class
            ],
            [
                'rules' => 'after:today',
                'result' => TestSchema::make()
                    ->set('type', 'string')
                    ->set('format', 'date')
                    ->set('description', 'Must be a date after ' . date('Y-m-d') . '.'),
            ],
            [
                'rules' => 'integer|after:today',
                'result' => FieldSetException::class
            ],
        ];
    }
}