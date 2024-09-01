<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverters;

use Illuminate\Validation\Rules\Enum;
use MalteKuhr\LaravelGpt\Tests\Services\SchemaService\RuleConverterTestCase;
use MalteKuhr\LaravelGpt\Tests\Support\Enums\IntegerEnum;
use MalteKuhr\LaravelGpt\Tests\Support\Enums\StringEnum;
use MalteKuhr\LaravelGpt\Tests\Support\TestSchema;
use Throwable;

class EnumRuleConverterTest extends RuleConverterTestCase
{
    /**
     * @return array{rules: string|array, result: array|TestSchema|Throwable}[]
     */
    public static function casesProvider(): array
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