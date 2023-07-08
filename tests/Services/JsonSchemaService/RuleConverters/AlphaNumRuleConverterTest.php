<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Exceptions\JsonSchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class AlphaNumRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'alpha_num',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[\p{L}\p{M}0-9]+$'),
            ],
            [
                'rules' => 'alpha_num:ascii',
                'result' => TestSchema::make()->set('type', 'string')->set('pattern', '^[a-zA-Z0-9]+$'),
            ],
            [
                'rules' => 'alpha_num:test',
                'result' => UnknownValidationOptionException::class
            ],
        ];
    }
}