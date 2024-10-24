<?php

namespace MalteKuhr\LaravelGpt\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MalteKuhr\LaravelGpt\Commands\Make\GptActionMakeCommand;
use MalteKuhr\LaravelGpt\Commands\Make\RuleConverterMakeCommand;
use MalteKuhr\LaravelGpt\Drivers\OpenAIDriver;
use MalteKuhr\LaravelGpt\Drivers\GeminiDriver;

class GptServiceProvider extends BaseServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/laravel-gpt.php', 'laravel-gpt');

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

            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
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
            ], 'laravel-gpt');

            $this->commands([
                GptActionMakeCommand::class,
                RuleConverterMakeCommand::class
            ]);
        }
    }
}
