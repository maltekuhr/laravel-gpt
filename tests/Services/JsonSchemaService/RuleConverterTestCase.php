<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService;

use MalteKuhr\LaravelGpt\Services\SchemaService\JsonSchemaService;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use MalteKuhr\LaravelGpt\Tests\TestCase;
use Throwable;

abstract class RuleConverterTestCase extends TestCase
{
    /**
     * @dataProvider casesProvider
     */
    public function testIfRuleConverterBehavesAsExpected($rules, $expected): void
    {
        try {
            $result = JsonSchemaService::convert([
                'test' => $rules
            ]);
        } catch (Throwable $exception) {
            if (is_string($expected)) {
                $this->assertInstanceOf($expected, $exception);
                return;
            } else if ($expected instanceof Throwable) {
                $result = $exception;
            } else {
                throw $exception;
            }
        }

        if ($expected instanceof TestSchema) {
            $expected = $expected->toArray();
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    abstract public static function casesProvider(): array;
}
