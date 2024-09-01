<?php

namespace MalteKuhr\LaravelGpt\Tests\Chats;

use MalteKuhr\LaravelGpt\Extensions\FillableGptChat;
use MalteKuhr\LaravelGpt\Tests\TestCase;

class HooksTest extends TestCase
{
    public function testIfSimpleChattingWorks()
    {
        $chat = FillableGptChat::make();
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
