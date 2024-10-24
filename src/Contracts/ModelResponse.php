<?php

namespace MalteKuhr\LaravelGpt\Contracts;

class ModelResponse
{
    /**
     * Create a new ModelResponse instance.
     *
     * @param array $result The result from the model
     * @param array $confidence The confidence scores for the result
     */
    public function __construct(
        public readonly array $result,
        public readonly array $confidence
    ) {}

    /**
     * Create a new ModelResponse instance.
     *
     * @param array $result
     * @param array $confidence
     * @return static
     */
    public static function make(array $result, array $confidence): static
    {
        return new static($result, $confidence);
    }
}
