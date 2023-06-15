<?php

namespace MalteKuhr\LaravelGPT\Tests\Unit;

use Illuminate\Support\Facades\Config;
use MalteKuhr\LaravelGPT\Exceptions\ApiKeyIsMissingException;
use MalteKuhr\LaravelGPT\Facades\OpenAI;
use MalteKuhr\LaravelGPT\Providers\GPTServiceProvider;
use MalteKuhr\LaravelGPT\Testing\OpenAIFake;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse;
use Orchestra\Testbench\TestCase;

class SetupTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createApplication();

        Config::set('laravel-gpt.api_key', 'test');

        (new GPTServiceProvider($this->app))->register();
    }

    public function test_if_api_key_is_required()
    {
        $this->expectException(ApiKeyIsMissingException::class);

        Config::set('laravel-gpt.api_key', null);

        OpenAI::chat()->create($this->getChatData());
    }

    /**
     * @dataProvider providerTestIfApiCanBeCalled
     */
    public function test_if_api_can_be_called($expected)
    {
        $this->app->singleton(ClientContract::class, fn () => new OpenAIFake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'content' => $expected
                        ]
                    ]
                ]
            ]),
        ]));

        $this->assertEquals(
            expected: OpenAI::chat()->create($this->getChatData())->choices[0]->message->content,
            actual: $expected
        );
    }

    public function providerTestIfApiCanBeCalled()
    {
        return [
            ['Awesome!'],
            ['Great!'],
            ['Super!']
        ];
    }

    private function getChatData()
    {
        return [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'How are you?',
                ]
            ]
        ];
    }
}