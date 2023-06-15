<?php

namespace MalteKuhr\LaravelGPT\Tests;

use MalteKuhr\LaravelGPT\Providers\GPTServiceProvider;
use MalteKuhr\LaravelGPT\Testing\OpenAIFake;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse;
use Orchestra\Testbench\TestCase as BasicTestCase;

class TestCase extends BasicTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        (new GPTServiceProvider($app))->register();
        $app->singleton(ClientContract::class, fn () => new OpenAIFake([
            CreateResponse::fake()
        ]));

        return $app;
    }
}