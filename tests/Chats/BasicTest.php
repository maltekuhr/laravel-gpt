<?php

namespace MalteKuhr\LaravelGPT\Tests\Chats;

use MalteKuhr\LaravelGPT\Exceptions\GPTChat\ErrorPatternFoundException;
use MalteKuhr\LaravelGPT\Exceptions\GPTChat\NoFunctionCallException;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTChat;
use MalteKuhr\LaravelGPT\Extensions\FillableGPTFunction;
use MalteKuhr\LaravelGPT\Models\ChatFunctionCall;
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

    public function testIfSimpleChattingWithSystemMessageWorks()
    {
        $chat = FillableGPTChat::make(
            systemMessage: fn () => 'Answer Laravel related questions!'
        );
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
}
