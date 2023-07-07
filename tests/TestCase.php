<?php

namespace MalteKuhr\LaravelGPT\Tests;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\ChatFunctionCall;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use MalteKuhr\LaravelGPT\Providers\GPTServiceProvider;
use MalteKuhr\LaravelGPT\Testing\OpenAIFake;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse;
use Orchestra\Testbench\TestCase as BasicTestCase;

class TestCase extends BasicTestCase
{
    public function getPackageProviders($app)
    {
        return [
            GPTServiceProvider::class
        ];
    }

    protected function setTestResponse(mixed $content = null, string|array|null $name = null, ?ChatFunctionCall $functionCall = null): void
    {
        $this->setTestResponses([
            [
                'content' => $content,
                'name' => $name,
                'functionCall' => $functionCall
            ]
        ]);
    }

    protected function setTestResponses(array $responses): void
    {
        $responses = array_map(fn ($response) => CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        ...ChatMessage::from(
                            role: ChatRole::ASSISTANT,
                            content: $response['content'] ?? null,
                            name: $response['name'] ?? null,
                            functionCall: $response['functionCall'] ?? null
                        )->toArray()
                    ]
                ]
            ]
        ]), $responses);

        $this->app->singleton(ClientContract::class, fn () => new OpenAIFake($responses));
    }
}