<?php

namespace MalteKuhr\LaravelGPT\Tests\Services\JsonSchemaService;

use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Tests\Support\TestGPTFunction;
use MalteKuhr\LaravelGPT\Tests\TestCase;

class NestedRulesTest extends TestCase
{

    /**
     * @dataProvider casesProvider
     */
    public function testIfBasicNestingWorks($rules, $result)
    {
        $function = new TestGPTFunction(
            function: fn () => true,
            rules: $rules,
        );

        $this->assertEquals($result, FunctionManager::make($function)->docs()['parameters']);
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
                        ],
                    ],
                    "required" => [
                        "profile"
                    ],
                ],
            ],
            [
                'rules' => [
                    'test' => 'required|array|min:1|max:10',
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
                            "minItems" => 1,
                            "maxItems" => 10,
                        ],
                    ],
                    "required" => [
                        "test"
                    ],
                ],
            ],
            [
                'rules' => [
                    'profile' => 'required',
                    'profile.*.id' => 'required|bool',
                    'profile.*.name' => 'required|string|between:0,256',
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
                                        "minLength" => 0,
                                        "maxLength" => 256,
                                    ]
                                ],
                                "required" => [
                                    "id",
                                    "name"
                                ],
                            ],
                        ],
                    ],
                    "required" => [
                        "profile"
                    ],
                ],
            ],
        ];
    }
}
