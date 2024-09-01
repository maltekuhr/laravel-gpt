<?php

namespace MalteKuhr\LaravelGpt\Tests;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Models\ChatFunctionCall;
use MalteKuhr\LaravelGpt\Models\ChatMessage;
use MalteKuhr\LaravelGpt\Providers\GptServiceProvider;
use MalteKuhr\LaravelGpt\Testing\OpenAIFake;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse;
use Orchestra\Testbench\TestCase as BasicTestCase;

class TestCase extends BasicTestCase
{
    public function getPackageProviders($app)
    {
        return [
            GptServiceProvider::class
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