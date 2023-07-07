<?php

namespace MalteKuhr\LaravelGPT\Tests\Actions;

use MalteKuhr\LaravelGPT\Exceptions\GPTChat\NoFunctionCallException;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTAction;
use MalteKuhr\LaravelGPT\Models\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Tests\TestCase;

class BasicTest extends TestCase
{
    public function testIfActionCanBeCalled()
    {
        $this->setTestResponse(
            functionCall: ChatFunctionCall::from(
                name: 'test',
                arguments: [
                    'foo' => 'bar'
                ]
            )
        );

        FillableGPTAction::make(
            systemMessage: fn () => 'Call the test function',
            function: function (string $foo) {
                $this->assertEquals('bar', $foo);
            },
            rules: fn () => [
                'foo' => 'required|string'
            ],
            functionName: fn () => 'test',
        )->send('Bar');
    }

    public function testIfActionCanHandleRecurringFunctionCallMistake()
    {
        $this->expectException(NoFunctionCallException::class);

        $this->setTestResponses([
            [
                'content' => 'This is wrong'
            ],
            [
                'content' => 'This is still wrong'
            ]
        ]);

        FillableGPTAction::make(
            systemMessage: fn () => 'Call the test function',
            function: fn () => null,
            rules: fn () => [
                'foo' => 'required|string'
            ],
            functionName: fn () => 'test',
        )->send('Bar');
    }

    public function testIfActionCanHandleSingleFunctionCallMistake()
    {
        $this->setTestResponses([
            [
                'content' => 'This is wrong'
            ],
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'test',
                    arguments: [
                        'foo' => 'bar'
                    ]
                )
            ]
        ]);

        FillableGPTAction::make(
            systemMessage: fn () => 'Call the test function',
            function: function (string $foo) {
                $this->assertEquals('bar', $foo);
            },
            rules: fn () => [
                'foo' => 'required|string'
            ],
            functionName: fn () => 'test',
        )->send('Bar');
    }
}
