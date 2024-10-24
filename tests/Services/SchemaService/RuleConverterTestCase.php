<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService;

use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use MalteKuhr\LaravelGpt\Tests\TestCase;
use MalteKuhr\LaravelGpt\Enums\SchemaType;
use Throwable;

abstract class RuleConverterTestCase extends TestCase
{
    /**
     * @dataProvider casesProvider
     */
    public function testIfRuleConverterBehavesAsExpected($rules, $expected): void
    {
        try {
            $actual = SchemaService::convert([
                'test' => $rules
            ], SchemaType::JSON);
        } catch (Throwable $exception) {
            if (is_string($expected)) {
                $this->assertInstanceOf($expected, $exception);
                return;
            } else if ($expected instanceof Throwable) {
                $actual = $exception;
            } else {
                throw $exception;
            }
        }

        if ($expected instanceof TestSchema) {
            $expected = $expected->toArray();
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    abstract public static function casesProvider(): array;
}
