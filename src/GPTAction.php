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
}