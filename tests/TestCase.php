<?php

namespace MalteKuhr\LaravelGpt\Tests;

use MalteKuhr\LaravelGpt\Providers\GptServiceProvider;
use Orchestra\Testbench\TestCase as BasicTestCase;

class TestCase extends BasicTestCase
{
    public function getPackageProviders($app)
    {
        return [
            GptServiceProvider::class
        ];
    }
}