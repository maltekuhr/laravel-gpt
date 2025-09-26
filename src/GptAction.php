<?php

namespace MalteKuhr\LaravelGpt;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use MalteKuhr\LaravelGpt\Facades\ActionManager;
use MalteKuhr\LaravelGpt\Data\ModelResponse;
use MalteKuhr\LaravelGpt\Helper\Dir;
use RuntimeException;

abstract class GptAction
{
    use Dir;

    /**
     * Create a new GptAction instance.
     *
     * @param array $parts
     * @param array $attributes
     * @param array $meta
     */
    public function __construct(
        protected array $parts,
        protected array $attributes = [],
        protected array $meta = [],
        protected ?ModelResponse $response = null
    ) {}

    /**
     * Define the validation rules for the response.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Run the action.
     *
     * @throws RuntimeException
     * @return self
     */
    public function run(int $tries = 3, bool $silent = false): self
    {
        ActionManager::run($this, $tries, $silent);

        return $this;
    }

    /**
     * Get the system message for the assistant.
     * 
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return null;
    }

    /**
     * Get the temperature for the response. (0 - 2)
     * 
     * @return ?float
     */
    public function temperature(): ?float
    {
        return null;
    }

    /**
     * Get the maximum token limit per request.
     * 
     * @return int|null
     */
    public function maxTokens(): ?int
    {
        return null;
    }

    /**
     * Get the model to be used for the request.
     * 
     * @return string
     */
    public function model(): string
    {
        return config('laravel-gpt.default_model');
    }

    /**
     * Get the parts of the action.
     *
     * @return array
     */
    public function parts(): array
    {
        return $this->parts;
    }

    /**
     * Get the confidence score using the provided calculator.
     * 
     * You can provide a $path for which the confidence score 
     * should be calculated. The path should be a dot separated
     * string that describes the path to the value in the response.
     *
     * @param ?string $path
     * @param ?ConfidenceCalculator $calculator = null
     * @return ?int
     */
    public function confidence(?string $path = null, ?ConfidenceCalculator $calculator = null): ?int
    {
        if (is_null($this->response)) {
            return null;
        }

        return $this->response->confidence($path, $calculator);
    }

    /**
     * Get the attributes of the action.
     *
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the meta information of the action.
     *
     * @return array
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Get the output for this action.
     *
     * @return ?array
     */
    public function output(): ?array
    {
        return $this->response?->output();
    }

    /**
     * Set the response for this action.
     *
     * @param ModelResponse $response
     * @return self
     */
    public function setResponse(ModelResponse $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Generate a unique SHA-256 hash for this action.
     * 
     * This hash is used for caching purposes and uniquely identifies the action
     * based on its serialized state, system message, model and validation rules.
     *
     * @return string The SHA-256 hash of the action
     */
    public function sha(): string
    {
        return hash('sha256', json_encode([
            'action' => serialize($this),
            'system_message' => $this->systemMessage(),
            'model' => $this->model(),
            'rules' => $this->rules(),
            'config' => config('laravel-gpt')
        ]));
    }

    /**
     * Get the trace ID associated with this action's response.
     * 
     * The trace ID is used to link the action to its execution trace in the database.
     * Returns null if no response has been set yet.
     *
     * @return int|null The trace ID or null if no response exists
     */
    public function traceId(): ?int
    {
        return $this->response?->traceId;
    }
}
