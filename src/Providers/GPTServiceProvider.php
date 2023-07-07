<?php

namespace MalteKuhr\LaravelGPT\Providers;

use Illuminate\Support\ServiceProvider;
use MalteKuhr\LaravelGPT\Commands\Make\GPTActionMakeCommand;
use MalteKuhr\LaravelGPT\Commands\Make\GPTChatMakeCommand;
use MalteKuhr\LaravelGPT\Commands\Make\GPTFunctionMakeCommand;
use MalteKuhr\LaravelGPT\Exceptions\ApiKeyIsMissingException;
use OpenAI;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;

class GPTServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/laravel-gpt.php', 'laravel-gpt');

        // Register the openai class to use with the facade
        $this->app->singleton(ClientContract::class, static function (): Client {
            $apiKey = config('laravel-gpt.api_key');
            $organization = config('laravel-gpt.organization');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw ApiKeyIsMissingException::create();
            }

            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => config('laravel-gpt.request_timeout')]))
                ->make();
        });

        $this->app->alias(ClientContract::class, 'openai');
        $this->app->alias(ClientContract::class, Client::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GPTFunctionMakeCommand::class,
                GPTChatMakeCommand::class,
                GPTActionMakeCommand::class
            ]);
        }

        $this->publishes([
            __DIR__.'/../../config/laravel-gpt.php' => config_path('laravel-gpt.php'),
        ], 'config');
    }
}
