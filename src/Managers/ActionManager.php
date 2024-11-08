<?php

namespace MalteKuhr\LaravelGpt\Managers;

use MalteKuhr\LaravelGpt\Contracts\Driver;
use MalteKuhr\LaravelGpt\Exceptions\InvalidJsonResponseException;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Models\GptTrace;
use Exception;

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
            if ($throw) {
                throw InvalidJsonResponseException::create($trace?->id, $validator->errors()->first());
            }

            return false;
        }

        return true;
    }
}
