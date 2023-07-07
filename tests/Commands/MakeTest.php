<?php

namespace MalteKuhr\LaravelGPT\Tests\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use MalteKuhr\LaravelGPT\Tests\TestCase;

class MakeTest extends TestCase
{
    public function testIfCreationWorks()
    {
        // delete LaravelGPT folder
        File::deleteDirectory(app_path('GPT'));

        $this->artisan('make:gpt-action TestGPTAction')
            ->expectsOutputToContain('INFO  GPTAction [App\GPT\Actions\Test\TestGPTAction.php] created successfully.')
            ->assertSuccessful();

        $this->assertFileExists(
            app_path('GPT/Actions/Test/TestGPTAction.php')
        );
    }

    /**
     * @depends testIfCreationWorks
     */
    public function testIfOverwriteProtectionWorks()
    {
        $this->artisan('make:gpt-action Test')
            ->expectsOutputToContain('ERROR  GPTAction already exists.')
            ->assertSuccessful();
    }

    /**
     * @depends testIfCreationWorks
     */
    public function testIfAutoSuffixWorks()
    {
        $this->artisan('make:gpt-action Test2')
            ->expectsOutputToContain('INFO  GPTAction [App\GPT\Actions\Test2\Test2GPTAction.php] created successfully.')
            ->assertSuccessful();

        $this->assertFileExists(
            app_path('GPT/Actions/Test2/Test2GPTAction.php')
        );
    }
}
