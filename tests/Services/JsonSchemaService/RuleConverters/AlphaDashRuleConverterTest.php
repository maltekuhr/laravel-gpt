<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Exceptions\SchemaService\UnknownValidationOptionException;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
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