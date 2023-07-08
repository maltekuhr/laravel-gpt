<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class AlphaDashRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'alpha_dash',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[\p{L}\p{M}0-9_-]+$'),
            ],
            [
                'rules' => 'alpha_dash:ascii',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[a-zA-Z0-9_-]+$'),
            ],
            [
                'rules' => 'alpha_dash:test',
                'result' => UnknownValidationOptionException::class
            ],
        ];
    }
}