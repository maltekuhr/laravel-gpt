<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class AlphaRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'alpha',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[\p{L}\p{M}]+$'),
            ],
            [
                'rules' => 'alpha:ascii',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[a-zA-Z]+$'),
            ],
            [
                'rules' => 'alpha:test',
                'result' => UnknownValidationOptionException::class
            ],
        ];
    }
}