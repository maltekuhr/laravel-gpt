<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use MalteKuhr\LaravelGpt\Services\SchemaService\JsonSchemaService;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class RequiredRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
    {
        return [
            [
                'rules' => 'required',
                'result' => TestSchema::make()->required(),
            ]
        ];
    }

    public function test_if_required_is_compatible_with_array_items()
    {
        $this->expectExceptionMessage('The required rule cannot be applied to array items. Use the min and max rules instead.');

        JsonSchemaService::convert([
            'test' => 'required',
            'test.*' => 'required|in:foo,bar',
        ]);
    }
}