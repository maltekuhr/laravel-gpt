<?php

namespace MalteKuhr\LaravelGpt\Tests\Actions;

use MalteKuhr\LaravelGpt\Exceptions\GptChat\NoFunctionCallException;
use MalteKuhr\LaravelGpt\Extensions\FillableGptAction;
use MalteKuhr\LaravelGpt\Models\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Tests\TestCase;

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

        FillableGptAction::make(
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

        FillableGptAction::make(
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

        FillableGptAction::make(
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
