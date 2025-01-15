<?php

namespace MalteKuhr\LaravelGpt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MalteKuhr\LaravelGpt\Models\GptTrace;
use MalteKuhr\LaravelGpt\Facades\ActionManager;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputFile;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputText;

use function Laravel\Prompts\text;
use function Laravel\Prompts\progress;

class ExportTrainingDataCommand extends Command
{
    protected $signature = 'gpt:export-training 
        {--key= : Dataset key (e.g., dataset-xyz)} 
        {--override : Override existing files}
        {--filter= : SQL WHERE clause for filtering traces (e.g., "created_at > \'2024-01-01\'")}';
    protected $description = 'Export GPT traces as training data';

    protected string $datasetKey;
    protected array $state;

    public function handle()
    {
        // Get key from parameter or prompt
        $this->datasetKey = $this->option('key') ?: text(
            label: 'Enter a dataset key',
            placeholder: 'e.g., dataset-xyz',
            required: true
        );
        
        // Create directory if it doesn't exist
        if (!File::exists($this->getBasePath())) {
            File::makeDirectory($this->getBasePath(), 0755, true);
        }

        // Get state
        $this->state = $this->getState();

        // Always use OpenAI connection
        $this->state['connection'] = 'openai';

        // Ask for WHERE clause if not provided via option
        $this->state['where'] = $this->option('filter') ?: text(
            label: 'Enter WHERE clause for filtering traces',
            placeholder: 'e.g., created_at > "2024-01-01"',
            default: $this->state['where'] ?? ''
        );

        // Initialize or get exported IDs
        $this->state['exported'] = $this->state['exported'] ?? [];
        $batch = ($this->state['batch'] ?? 0) + 1;
        
        // Store updated state
        $this->storeState([...$this->state, 'batch' => $batch]);

        // Get traces using the model
        $traces = GptTrace::whereRaw($this->state['where'])->get();

        if ($traces->isEmpty()) {
            $this->error('No traces found matching the filter criteria.');
            return;
        }

        $progress = progress( 
            label: 'Exporting training data',
            steps: $traces->count()
        );

        $skipped = 0;
        $exported = 0;
        $failed = 0;

        foreach ($traces as $trace) {
            // Find any existing files for this trace ID
            $existingFiles = collect(File::glob($this->getBasePath() . "/{$trace->id}/training.json"));
            
            if ($existingFiles->isNotEmpty() && !$this->option('override')) {
                $skipped++;
                $progress->advance();
                continue;
            }

            // Delete old files before attempting new export if override is enabled
            if ($this->option('override')) {
                $existingFiles->each(function ($file) {
                    $folder = dirname($file);
                    File::deleteDirectory($folder);
                });
            }

            $result = $this->exportTrace($trace, $batch);
            
            if ($result === false) {
                $failed++;
            } else {
                $exported++;
                $this->state['exported'][] = $trace->id;
                $this->storeState($this->state);
            }

            $progress->advance();
        }

        $progress->finish();

        $this->newLine();
        $this->info("Export completed:");
        $this->line(" - Connection: {$this->state['connection']}");
        $this->line(" - Exported: {$exported} files");
        $this->line(" - Skipped: {$skipped} files (already existed)");
        $this->line(" - Failed: {$failed} files");
        $this->line(" - Location: {$this->getBasePath()}");

        if ($skipped > 0 && !$this->option('override')) {
            $this->info("Tip: Use --override option to override existing files");
        }
    }

    protected function getBasePath(): string
    {
        return base_path('datasets/' . $this->datasetKey);
    }

    protected function getStatePath(): string
    {
        return $this->getBasePath() . '/state.json';
    }

    protected function getState(): array
    {
        return File::exists($this->getStatePath())
            ? json_decode(File::get($this->getStatePath()), true)
            : [];
    }

    protected function exportTrace(GptTrace $trace, int $batch): bool
    {
        $folder = $this->getBasePath() . "/{$trace->id}";
        $filePath = $folder . "/training.json";

        
        try {
            // Get the action instance from the trace
            $action = $trace->getAction();
            
            if (!$action) {
                $this->warn("Failed to export trace {$trace->id}: Could not get action from trace");
                return false;
            }

            // Get the training data using ActionManager with specific connection
            $trainingData = ActionManager::training($action, $this->state['connection']);
            
            if (empty($trainingData)) {
                $this->warn("Failed to export trace {$trace->id}: Training data is empty");
                return false;
            }
            
            // Create directory if it doesn't exist
            if (!File::exists($folder)) {
                File::makeDirectory($folder, 0755, true);
            }
            
            File::put($filePath, json_encode($trainingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            foreach ($action->parts() as $index => $part) {
                if ($part instanceof InputFile) {
                    File::put($folder . "/part-{$index}." . $part->extension, base64_decode($part->content));
                } else if ($part instanceof InputText) {
                    File::put($folder . "/part-{$index}.txt", $part->text);
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Failed to export trace {$trace->id}:");
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            
            if (config('app.debug')) {
                $this->error("Stack trace:");
                $this->error($e->getTraceAsString());
            }
            
            return false;
        }
    }

    protected function storeState(array $state): void
    {
        File::put($this->getStatePath(), json_encode($state, JSON_PRETTY_PRINT));
    }
} 
