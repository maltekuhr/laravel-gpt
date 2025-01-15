<?php

namespace MalteKuhr\LaravelGpt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CombineTrainingDataCommand extends Command
{
    protected $signature = 'gpt:combine-training {--key= : Dataset key to combine} {--share=90 : Percentage of data for training}';
    protected $description = 'Combine training files into a single JSONL file';

    public function handle()
    {
        $datasetKey = $this->option('key') ?: $this->ask('Enter the dataset key to combine');
        $trainingShare = min(100, max(1, intval($this->option('share'))));
        $basePath = base_path('datasets/' . $datasetKey);

        if (!File::exists($basePath)) {
            $this->error("Dataset not found: {$datasetKey}");
            return;
        }

        // Recursively get all training.json files from subdirectories
        $files = collect(File::allFiles($basePath))
            ->filter(function($file) {
                return $file->getFilename() === 'training.json';
            })->shuffle();

        if ($files->isEmpty()) {
            $this->error('No training files found');
            return;
        }

        $timestamp = now()->format('Y-m-d-H-i-s');
        $trainingCount = ceil($files->count() * ($trainingShare / 100));
        $validationCount = $files->count() - $trainingCount;

        $trainingFiles = $files->take($trainingCount);
        $validationFiles = $files->skip($trainingCount);

        $trainingPath = $basePath . '/training-' . $timestamp . '.jsonl';
        $validationPath = $basePath . '/validation-' . $timestamp . '.jsonl';

        $this->processFiles($trainingFiles, $trainingPath, 'Training');
        $this->processFiles($validationFiles, $validationPath, 'Validation');

        $this->info("Training data ({$trainingShare}%) saved to {$trainingPath}");
        $this->info("Validation data (" . (100 - $trainingShare) . "%) saved to {$validationPath}");
    }

    protected function processFiles($files, $outputPath, $label)
    {
        $handle = fopen($outputPath, 'w');
        
        $bar = $this->output->createProgressBar($files->count());
        $bar->start();
        
        foreach ($files as $file) {
            $content = File::get($file);
            $content = json_decode($content, true);
            $content['messages'][2]['content'] = str_replace('\\', '', json_encode($content['messages'][2]['content'], JSON_UNESCAPED_UNICODE));
            unset($content['response_format']);
            fwrite($handle, json_encode($content, JSON_UNESCAPED_UNICODE) . "\n");
            $bar->advance();
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
    }
} 