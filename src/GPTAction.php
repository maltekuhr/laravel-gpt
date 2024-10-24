<?php

namespace MalteKuhr\LaravelGpt;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use MalteKuhr\LaravelGpt\Facades\ActionManager;
use MalteKuhr\LaravelGpt\Helper\Dir;
use MalteKuhr\LaravelGpt\Contracts\ModelResponse;
use RuntimeException;

abstract class GptAction
{
    use Dir;

    /**
     * The response from the model.
     *
     * @var array|null
     */
    protected ?array $response = null;

    /**
     * The confidence scores for the response.
     *
     * @var array|null
     */
    protected ?array $confidence = null;

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
        protected array $meta = []
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
     * @param ?ConfidenceCalculator $calculator = null
     * @return ?int
     */
    public function confidence(?ConfidenceCalculator $calculator = null): ?int
    {
        if (!$this->confidence) {
            return null;
        }

        return min(100, max(0, $calculator?->confidence($this->confidence) ?? (int)round(array_sum($this->confidence) / count($this->confidence))));
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
     * Get the response for this action.
     *
     * @return ?array
     */
    public function response(): ?array
    {
        return $this->response;
    }

    /**
     * Handle the response and confidence scores for this action.
     *
     * @param ModelResponse $response
     * @return self
     */
    public function handleModelResponse(ModelResponse $response): self
    {
        $this->response = $response->result;
        
        if ($response->confidence !== null) {
            $this->confidence = $response->confidence;
        }

        return $this;
    }
}