<?php

namespace MalteKuhr\LaravelGPT\Tests\Chats;

use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Exceptions\GPTAction\NoFunctionCallException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTAction;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTChat;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTFunction;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Models\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use MalteKuhr\LaravelGPT\Tests\TestCase;

class BasicTest extends TestCase
{
    public function testIfSimpleChattingWorks()
    {
        $chat = FillableGPTChat::make();
        $chat->addMessage('Laravel is a...');

        $this->setTestResponses($answers = [
            [
                'content' => 'Laravel is a PHP framework for web application development with elegant syntax.'
            ],
            [
                'content' => 'Taylor Otwell'
            ],
        ]);

        $this->assertEquals(
            $answers[0]['content'],
            $chat->send()->latestMessage()->content
        );

        $chat->addMessage('How is the creator of Laravel?');

        $this->assertEquals(
            $answers[1]['content'],
            $chat->send()->latestMessage()->content
        );
    }

    public function testIfFunctionCallWorks()
    {
        $chat = FillableGPTChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGPTFunction(
                    name: fn () => 'search_documentation',
                    description: fn () => 'Searches the Laravel documentation',
                    function: fn () => function (string $query) {
                        return [
                            'result' => 'composer create-project laravel/laravel example-app'
                        ];
                    },
                    rules: fn () => [
                        'query' => 'required|string|max:255'
                    ]
                )
            ]
        );

        $chat->addMessage('How to install Laravel?');

        $this->setTestResponses($answers = [
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'search_documentation',
                    arguments: [
                        'query' => 'Laravel Installation Command'
                    ]
                )
            ],
            [
                'content' => 'You can install Laravel using `composer create-project laravel/laravel example-app`!'
            ],
        ]);

        $this->assertEquals(
            $answers[1]['content'],
            $chat->send()->latestMessage()->content
        );
    }

    public function testIfFunctionCallForcingWorks()
    {
        $this->assertThrows(function () {
            $chat = FillableGPTChat::make(
                systemMessage: fn () => 'Answer Laravel related questions!',
                functions: fn () => [
                    new FillableGPTFunction(
                        name: fn () => 'search_documentation',
                        description: fn () => 'Searches the Laravel documentation',
                        function: fn () => function (string $query) {
                            return [
                                'result' => 'composer create-project laravel/laravel example-app'
                            ];
                        },
                        rules: fn () => [
                            'query' => 'required|string|max:255'
                        ]
                    )
                ],
                functionCall: fn () => FillableGPTFunction::class
            );

            $chat->addMessage('How to install Laravel?');

            $this->setTestResponses([
                [
                    'content' => 'You can install Laravel using `composer create-project laravel/laravel example-app`!'
                ],
                [
                    'content' => 'You can install Laravel using `composer create-project laravel/laravel example-app`!'
                ],
            ]);

            $chat->send();
        }, NoFunctionCallException::class);
    }

    public function testIfFunctionCallForcingCorrectionWorks()
    {
        $chat = FillableGPTChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGPTFunction(
                    name: fn () => 'search_documentation',
                    description: fn () => 'Searches the Laravel documentation',
                    function: fn () => function (string $query) {
                        return [
                            'result' => 'composer create-project laravel/laravel example-app'
                        ];
                    },
                    rules: fn () => [
                        'query' => 'required|string|max:255'
                    ]
                )
            ],
            functionCall: fn () => FillableGPTFunction::class
        );

        $chat->addMessage('How to install Laravel?');

        $this->setTestResponses($answers = [
            [
                'content' => 'Wrong message!'
            ],
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'search_documentation',
                    arguments: [
                        'query' => 'Laravel Installation Command'
                    ]
                )
            ]
        ]);

        $this->assertEquals(
            $answers[1]['functionCall']->name,
            $chat->send()->latestMessage()->name
        );
    }
}
