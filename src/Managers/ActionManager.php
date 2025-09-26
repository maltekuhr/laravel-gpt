<?php

namespace MalteKuhr\LaravelGpt\Managers;

use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Exceptions\InvalidJsonResponseException;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Models\GptTrace;
use MalteKuhr\LaravelGpt\Implementations\Parts\InputFile;
use Exception;
use Illuminate\Support\Facades\Log;
use MalteKuhr\LaravelGpt\Data\ModelResponse;

class ActionManager
{
    /**
     * Sends the current conversation using the appropriate driver.
     *
     * @param GptAction $action
     * @param int $tries
     * @return array
     * 
     * @throws InvalidJsonResponseException
     * @throws Exception
     */
    public function run(GptAction $action, int $tries = 1, bool $silent = false): array
    {
        $model = $action->model();
        $modelConfig = config('laravel-gpt.models')[$model] ?? null;

        // check if the model is configured
        if (!$modelConfig) {
            throw new Exception("Model '{$model}' not found in configuration.");
        }

        // ensure a valid number of tries is set
        if ($tries < 1) {
            throw new Exception("Tries must be at least 1.");
        }

        // get the connection for the model
        $driver = app("laravel-gpt.{$modelConfig['connection']}");

        // ensure the driver is valid
        if (!$driver instanceof Driver) {
            throw new Exception("Invalid driver for connection '{$modelConfig['connection']}'.");
        }

        // ensure files are stored
        $this->ensureFilesStored($action);


        $response = null;
        for ($try = 0; $try < $tries; $try++) {
            try {
                $response = $driver->run($action);
            } catch (Exception $e) {
                if ($try === $tries - 1) {
                    throw $e;
                } else {
                    continue;
                }
            }

            if (!$silent) {
                $trace = GptTrace::trace($action, $response);

                $response = ModelResponse::fromArray([
                    ...$response->toArray(),
                    'traceId' => $trace?->id
                ]);
            }

            // log the response if verbose mode is enabled
            if (config('laravel-gpt.verbose')) {
                Log::info('Model response', [
                    'data' => json_encode($response->output())
                ]);
            }

            if ($this->validate($action, $trace ?? null, $response->output(), throw: $try === $tries - 1)) {
                break;
            }
        }

        // save response 
        $action->setResponse($response);

        return $response->output();
    }

    /**
     * Validates the response from the GPT model against the action's rules.
     *
     * @param GptAction $action
     * @param array $response
     * @param bool $throw
     * @return bool
     * 
     * @throws InvalidFunctionCallException
     */
    protected function validate(GptAction $action, ?GptTrace $trace, array $response, bool $throw = false): bool
    {
        $validator = validator($response, $action->rules());

        if ($validator->fails()) {
            dd($response, $validator->errors());
            if ($throw) {
                throw InvalidJsonResponseException::create($trace?->id, $validator->errors()->first());
            }

            return false;
        }

        return true;
    }

    /**
     * Map the parts and ensure files are stored.
     *
     * @param GptAction $action
     * @return void
     */
    protected function ensureFilesStored(GptAction $action): void
    {
        foreach ($action->parts() as $part) {
            if ($part instanceof InputFile) {
                $part->ensureStored();
            }
        }
    }

    public function training(GptAction $action, ?string $connection = null): ?array
    {
        if ($connection) {
            $driver = app("laravel-gpt.{$connection}");
        } else {
            $model = $action->model();
            $modelConfig = config('laravel-gpt.models')[$model] ?? null;

            // check if the model is configured
            if (!$modelConfig) {
                throw new Exception("Model '{$model}' not found in configuration.");
            }

            $connection = $modelConfig['connection'];
            $driver = app("laravel-gpt.{$connection}");
        }

        // ensure the driver is valid
        if (!$driver instanceof Driver) {
            throw new Exception("Invalid driver for connection '{$connection}'.");
        }

        return $driver->training($action);
    }
}
