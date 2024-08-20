<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Services\SchemaService\CustomRules\FieldDescription;
use MalteKuhr\LaravelGPT\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use Throwable;

class FieldDescriptionRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => [FieldDescription::set('foo')],
                'result' => TestSchema::make()->set('description', 'foo'),
            ],
            [
                'rules' => [FieldDescription::set('foo'), FieldDescription::set('bar')],
                'result' => TestSchema::make()->set('description', 'foo; bar'),
            ]
        ];
    }
}