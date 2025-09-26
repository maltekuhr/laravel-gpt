<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\SchemaService;

use MalteKuhr\LaravelGpt\Enums\SchemaType;
use MalteKuhr\LaravelGpt\Services\SchemaService\SchemaService;
use MalteKuhr\LaravelGpt\Tests\TestCase;

class NestedRulesTest extends TestCase
{

    /**
     * @dataProvider casesProvider
     */
    public function testIfBasicNestingWorks($rules, $result)
    {
        $schema = SchemaService::convert($rules, SchemaType::JSON);

        $this->assertEquals($result, $schema);
    }

    public static function casesProvider(): array
    {
        return [
            [
                'rules' => [
                    'profile' => 'required',
                    'profile.id' => 'required|bool',
                ],
                'result' => [
                    "type" => "object",
                    "properties" => [
                        "profile" => [
                            "type" => "object",
                            "properties" => [
                                "id" => [
                                    "type" => "boolean"
                                ]
                            ],
                            "required" => [
                                "id"
                            ],
                            'additionalProperties' => false,
                        ],
                    ],
                    "required" => [
                        "profile"
                    ],
                    'additionalProperties' => false,
                ],
            ],
            [
                'rules' => [
                    'test' => 'array|min:1|max:10',
                    'test.*' => 'in:foo,bar',
                ],
                'result' => [
                    "type" => "object",
                    "properties" => [
                        "test" => [
                            "type" => "array",
                            "items" => [
                                "type" => "string",
                                "enum" => [
                                    "foo",
                                    "bar"
                                ]
                            ],
                            "description" => "The array must not have more than 10 items.; The array must have at least 1 items.",
                        ],
                    ],
                    "required" => [
                        "test"
                    ],
                    'additionalProperties' => false,
                ],
            ],
            [
                'rules' => [
                    'profile.*.id' => 'bool',
                    'profile.*.name' => 'string|between:0,256',
                ],
                'result' => [
                    "type" => "object",
                    "properties" => [
                        "profile" => [
                            "type" => "array",
                            "items" => [
                                "type" => "object",
                                "properties" => [
                                    "id" => [
                                        "type" => "boolean"
                                    ],
                                    "name" => [
                                        "type" => "string",
                                        "description" => "The string must be between 0 and 256 characters long.",
                                    ]
                                ],
                                "required" => [
                                    "id",
                                    "name"
                                ],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    "required" => [
                        "profile"
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
