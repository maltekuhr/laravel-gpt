<?php

namespace MalteKuhr\LaravelGpt\Tests\Chats;

use MalteKuhr\LaravelGpt\Exceptions\GptChat\ErrorPatternFoundException;
use MalteKuhr\LaravelGpt\Exceptions\GptChat\NoFunctionCallException;
use MalteKuhr\LaravelGpt\Extensions\FillableGptChat;
use MalteKuhr\LaravelGpt\Extensions\FillableGptFunction;
use MalteKuhr\LaravelGpt\Models\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Tests\TestCase;

class FunctionCallTest extends TestCase
{
    public function testIfFunctionCallWorks()
    {
        $chat = FillableGptChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGptFunction(
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
        $this->expectException(NoFunctionCallException::class);

        $chat = FillableGptChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGptFunction(
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
            functionCall: fn () => FillableGptFunction::class
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
    }

    public function testIfFunctionCallForcingCorrectionWorks()
    {
        $chat = FillableGptChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGptFunction(
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
            functionCall: fn () => FillableGptFunction::class
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

    public function testIfFunctionCallErrorsRetryWorks()
    {
        $chat = FillableGptChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGptFunction(
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
            functionCall: fn () => FillableGptFunction::class
        );

        $chat->addMessage('How to install Laravel?');

        $this->setTestResponses($answers = [
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'search_documentation',
                    arguments: [
                        'query' => null
                    ]
                )
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

    public function testIfFunctionCallErrorsPatternDetectionWorks()
    {
        $this->expectException(ErrorPatternFoundException::class);

        $chat = FillableGptChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!',
            functions: fn () => [
                new FillableGptFunction(
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
            functionCall: fn () => FillableGptFunction::class
        );

        $chat->addMessage('How to install Laravel?');

        $this->setTestResponses([
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'search_documentation',
                    arguments: [
                        'query' => null
                    ]
                )
            ],
            [
                'functionCall' => ChatFunctionCall::from(
                    name: 'search_documentation',
                    arguments: [
                        'query' => null
                    ]
                )
            ],
        ]);

        $chat->send();
    }
}
