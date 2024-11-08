<?php

namespace MalteKuhr\LaravelGpt\Data;

use MalteKuhr\LaravelGpt\Facades\JsonPathFinder;
use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use Illuminate\Support\Arr;

class ModelResponse
{
    /**
     * Create a new ModelResponse instance.
     *
     * @param string $output
     * @param TokenConfidence[] $confidence
     * @param string $uuid
     */
    public function __construct(
        public readonly string $output,
        public readonly array $confidence,
        public readonly string $uuid
    ) {}

    /**
     * Create a new ModelResponse instance.
     *
     * @param string $output
     * @param TokenConfidence[] $confidence
     * @param string $uuid
     * @return static
     */
    public static function make(string $output, array $confidence, string $uuid): static
    {
        return new static($output, $confidence, $uuid);
    }

    /**
     * Convert the ModelResponse instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'output' => $this->output,
            'confidence' => array_map(fn (TokenConfidence $confidence) => $confidence->toArray(), $this->confidence),
            'uuid' => $this->uuid
        ];
    }

    /**
     * Create a new ModelResponse instance from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            output: $data['output'],
            confidence: array_map(fn(array $confidence) => TokenConfidence::fromArray($confidence), $data['confidence']),
            uuid: $data['uuid']
        );
    }

    /**
     * Get output as array.
     *
     * @return array
     */
    public function output(): array
    {
        return json_decode($this->output, true);
    }

    /**
     * Calculate the confidence score for the entire output or a specific path.
     *
     * @param string|null $path
     * @param ConfidenceCalculator|null $calculator
     * @return int|null
     */
    public function confidence(string $path = null, ?ConfidenceCalculator $calculator = null): ?int
    {
        if (!is_null($path)) {
            $tokens = $this->relevantTokens($path);
        } else {
            $tokens = $this->confidence;
        }

        if ($calculator) {
            return $calculator->confidence($tokens);
        }

        return array_sum(array_map(fn (TokenConfidence $token) => $token->confidence, $tokens)) / count($tokens);
    }

    /**
     * Get the tokens relevant to a specific JSON path in the output.
     *
     * @param string|null $path
     * @return array
     * @throws \Exception
     */
    protected function relevantTokens(string $path = null): array
    {
        if (is_null($path) || !Arr::exists($this->output(), $path)) {
            throw new \Exception("Path '{$path}' not found in output");
        }

        $positions = JsonPathFinder::findPosition($this->output, $path);

        if (is_null($positions)) {
            dd($this->output, $path);
        }

        $endIndex = 0;
        $relevantTokens = [];
        foreach ($this->confidence as $token) {
            $startIndex = $endIndex;
            $endIndex += $token->length();

            if ($endIndex > $positions['start'] && $startIndex < $positions['end']) {
                $relevantTokens[] = $token;
            }
        }

        return $relevantTokens;
    }
}
