<?php

namespace MalteKuhr\LaravelGpt\Tests\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use MalteKuhr\LaravelGpt\Tests\TestCase;

class MakeTest extends TestCase
{
    public function testIfCreationWorks()
    {
        // delete LaravelGpt folder
        File::deleteDirectory(app_path('Gpt'));

        $this->artisan('make:gpt-action TestGptAction')
            ->expectsOutputToContain('INFO  GptAction [App\Gpt\Actions\Test\TestGptAction.php] created successfully.')
            ->assertSuccessful();

        $this->assertFileExists(
            app_path('Gpt/Actions/Test/TestGptAction.php')
        );
    }

    /**
     * @depends testIfCreationWorks
     */
    public function testIfOverwriteProtectionWorks()
    {
        $this->artisan('make:gpt-action Test')
            ->expectsOutputToContain('ERROR  GptAction already exists.')
            ->assertSuccessful();
    }

    /**
     * @depends testIfCreationWorks
     */
    public function testIfAutoSuffixWorks()
    {
        $this->artisan('make:gpt-action Test2')
            ->expectsOutputToContain('INFO  GptAction [App\Gpt\Actions\Test2\Test2GptAction.php] created successfully.')
            ->assertSuccessful();

        $this->assertFileExists(
            app_path('Gpt/Actions/Test2/Test2GptAction.php')
        );
    }
}
