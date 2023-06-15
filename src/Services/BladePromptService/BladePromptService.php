<?php

namespace MalteKuhr\LaravelGPT\Services\BladePromptService;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

class BladePromptService
{
    /**
     * @var Factory
     */
    protected Factory $viewFactory;

    /**
     * Creates a new instance of the BladePromptService.
     *
     * @param string $path
     */
    protected function __construct(string $path)
    {
        $filesystem = new Filesystem;
        $compiler = new BladeCompiler($filesystem, storage_path('framework/views'));

        $engineResolver = new EngineResolver;
        $engineResolver->register('blade', function () use ($compiler) {
            return new CompilerEngine($compiler);
        });

        $this->viewFactory = new Factory($engineResolver, new FileViewFinder($filesystem, [$path]), new Dispatcher(new Container));
    }

    /**
     * Renders a blade file and returns the results as a string.
     *
     * @param string $path e.g. __DIR__
     * @param string $view e.g. 'system'
     * @param array $data
     * @return string
     */
    public static function render(string $path, string $view, array $data = []): string
    {
        return (new static($path))->viewFactory->make($view, $data)->render();
    }
}