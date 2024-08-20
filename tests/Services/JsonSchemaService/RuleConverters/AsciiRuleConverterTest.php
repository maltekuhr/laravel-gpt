<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\SchemaService\FieldSetException;
use MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverterTestCase;
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