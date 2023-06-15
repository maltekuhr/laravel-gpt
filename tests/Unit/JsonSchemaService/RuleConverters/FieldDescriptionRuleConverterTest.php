<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use MalteKuhr\LaravelGPT\Services\JsonSchemaService\CustomRules\FieldDescription;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class FieldDescriptionRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
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