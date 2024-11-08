<?php

namespace MalteKuhr\LaravelGpt\Tests\Services\JsonPathFinderService;

use MalteKuhr\LaravelGpt\Facades\JsonPathFinder;
use MalteKuhr\LaravelGpt\Services\JsonPathFinderService\JsonPathFinderService;
use MalteKuhr\LaravelGpt\Tests\TestCase;

class BasicTest extends TestCase
{
    /**
     * @dataProvider casesProvider
     */
    public function testJsonPathFinder($json, $path, $expectedResult)
    {
        $result = JsonPathFinder::findPosition($json, $path);

        $this->assertEquals($expectedResult, $result);
    }

    public static function casesProvider(): array
    {
        return [
            'simple key-value pair' => [
                'json' => '{"name": "John Doe", "age": 30}',
                'path' => 'name',
                'expectedResult' => [
                    'start' => 10,
                    'end' => 18
                ]
            ],
            'nested value first item' => [
                'json' => '{"user": {"name": "John Doe", "age": 30}}',
                'path' => 'user.name',
                'expectedResult' => [
                    'start' => 19,
                    'end' => 27
                ]
            ],
            'nested value last item' => [
                'json' => '{"user": {"name": "John Doe", "age": 30}}',
                'path' => 'user.age',
                'expectedResult' => [
                    'start' => 37,
                    'end' => 39
                ]
            ],
            'array value' => [
                'json' => '{"users": ["John", "Jane", "Doe"]}',
                'path' => 'users.1',
                'expectedResult' => [
                    'start' => 20,
                    'end' => 24
                ]
            ],
            'non-existent path' => [
                'json' => '{"name": "John Doe", "age": 30}',
                'path' => 'address',
                'expectedResult' => null
            ],
            'complex nested structure' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test.0.test',
                'expectedResult' => [
                    'start' => 22,
                    'end' => 26
                ]
            ],
            'complex nested structure 2' => [
                'json' => '{"title":"This is a title",   "description":{"content":[{"type":"text","lines":["This is a test line!"]}]}}',
                'path' => 'description',
                'expectedResult' => [
                    'start' => 44,
                    'end' => 106
                ]
            ],
            'complex nested structure with array' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test2.0.0.0',
                'expectedResult' => [
                    'start' => 104,
                    'end' => 108
                ]
            ],
            'complex nested structure with object' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test3.number',
                'expectedResult' => [
                    'start' => 169,
                    'end' => 172
                ]
            ],
            'complex nested structure with string' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test3.string',
                'expectedResult' => [
                    'start' => 183,
                    'end' => 187
                ]
            ],
            'complex nested structure with non-existent path' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test4',
                'expectedResult' => null
            ],
            'complex nested structure with path for object' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test.0',
                'expectedResult' => [
                    'start' => 9,
                    'end' => 28
                ]
            ],
            'complex nested structure with path for object. second item' => [
                'json' => '{"test":[{"test"    :"test"},{"test":"test"},   {"test2":"test"}],"test2":[{"a":"test  123","b":2,"0":["test"]},{"test":"test"},{"a":"test123","b":2}],"test3":{"number":123,"string":"test"}}',
                'path' => 'test.1',
                'expectedResult' => [
                    'start' => 29,
                    'end' => 47
                ]
            ],
            'object with null value' => [
                'json' => '{"einheit": "St", "intervall": null}',
                'path' => 'intervall',
                'expectedResult' => [
                    'start' => 31,
                    'end' => 35
                ]
            ]
        ];
    }

    /** @test */
    public function it_throws_exception_for_invalid_json()
    {
        $this->expectException(\JsonException::class);

        $invalidJson = '{invalid: json}';
        JsonPathFinder::findPosition($invalidJson, 'invalid');
    }
}

