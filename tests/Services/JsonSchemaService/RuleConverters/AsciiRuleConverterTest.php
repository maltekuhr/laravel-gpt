<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class AsciiRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'ascii',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[\x00-\x7F]+$'),
            ],
            [
                'rules' => 'integer|ascii',
                'result' => FieldSetException::class,
            ]
        ];
    }
}