<?php

namespace MalteKuhr\LaravelGPT\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MalteKuhr\LaravelGPT\Commands\Make\GPTActionMakeCommand;
use MalteKuhr\LaravelGPT\Commands\Make\GPTChatMakeCommand;
use MalteKuhr\LaravelGPT\Commands\Make\GPTFunctionMakeCommand;
use MalteKuhr\LaravelGPT\Commands\Make\RuleConverterMakeCommand;
use MalteKuhr\LaravelGPT\Managers\ChatManager;
use MalteKuhr\LaravelGPT\Managers\FunctionManager;
use MalteKuhr\LaravelGPT\Drivers\OpenAIDriver;
use MalteKuhr\LaravelGPT\Drivers\GeminiDriver;
use Exception;

class GPTServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/laravel-gpt.php', 'laravel-gpt');

        $this->app->singleton(ChatManager::class);
        $this->app->singleton(FunctionManager::class);

        // register all connections
        foreach (config('laravel-gpt.connections') as $name => $connection) {
            $driver = match ($connection['driver']) {
                'openai' => OpenAIDriver::class,
                'gemini' => GeminiDriver::class,
                default => null,
            };

            if ($driver === null) {
                continue;
            }

            $this->app->singleton("laravel-gpt.{$name}", fn ($app) => new $driver($name));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/laravel-gpt.php' => config_path('laravel-gpt.php'),
                __DIR__.'/../../database/migrations/2024_07_20_112821_create_gpt_chats_table.php' => database_path('migrations/2024_07_20_112821_create_gpt_chats_table.php'),
            ], 'laravel-gpt');

            $this->commands([
                GPTFunctionMakeCommand::class,
                GPTChatMakeCommand::class,
                GPTActionMakeCommand::class,
                RuleConverterMakeCommand::class
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ChatManager::class,
            FunctionManager::class,
        ];
    }
}
