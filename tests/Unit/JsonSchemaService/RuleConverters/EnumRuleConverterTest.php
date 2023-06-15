<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverters;

use Illuminate\Validation\Rules\Enum;
use MalteKuhr\LaravelGPT\Tests\Support\Enums\IntegerEnum;
use MalteKuhr\LaravelGPT\Tests\Support\Enums\StringEnum;
use MalteKuhr\LaravelGPT\Tests\Support\TestSchema;
use MalteKuhr\LaravelGPT\Tests\Unit\JsonSchemaService\RuleConverterTestCase;
use Throwable;

class EnumRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public function casesProvider(): array
    {
        return [
            [
                'rules' => [new Enum(IntegerEnum::class)],
                'result' => TestSchema::make()->set('type', 'integer')->set('enum', array_column(IntegerEnum::cases(), 'value')),
            ],
            [
                'rules' => [new Enum(StringEnum::class)],
                'result' => TestSchema::make()->set('type', 'string')->set('enum', array_column(StringEnum::cases(), 'value')),
            ]
        ];
    }
}