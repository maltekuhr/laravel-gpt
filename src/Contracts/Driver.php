<?php

namespace MalteKuhr\LaravelGpt\Contracts;

use Illuminate\Support\Arr;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Data\ModelResponse;

interface Driver
{
    /**
     * Constructor for the driver.
     *
     * @param string $connection
     */
    public function __construct(string $connection);

    /**
     * Run the AI model for the given action.
     *
     * @param GptAction $action
     * 
     * @return ModelResponse
     */
    public function run(GptAction $action): ModelResponse;

    /**
     * Create a training example for the given action.
     *
     * @param GptAction $action
     * 
     * @return ?array
     */
    public function training(GptAction $action): ?array;
}
