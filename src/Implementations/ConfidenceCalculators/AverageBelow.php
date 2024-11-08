<?php

namespace MalteKuhr\LaravelGpt\Implementations\ConfidenceCalculators;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use MalteKuhr\LaravelGpt\Data\TokenConfidence;

class AverageBelow implements ConfidenceCalculator
{
    /**
     * @param int $percentile The percentile threshold for confidence scores
     */
    public function __construct(
        private readonly int $percentile
    ) {}

    /**
     * Return the confidence score.
     *
     * @param TokenConfidence[] $tokens
     * @return int Number between 0 and 100
     */
    public function confidence(array $tokens): int
    {
        if (empty($tokens)) {
            return 0;
        }

        $confidenceScores = array_map(fn (TokenConfidence $token) => $token->confidence, $tokens);
        sort($confidenceScores);
        
        $threshold = $confidenceScores[(int)floor(count($confidenceScores) * $this->percentile / 100)];
        
        $validScores = array_filter($confidenceScores, fn ($score) => $score <= $threshold);
        
        if (empty($validScores)) {
            return 0;
        }

        $average = array_sum($validScores) / count($validScores);
        
        return min(100, max(0, (int)round($average)));
    }

    /**
     * Create a new instance of AverageBelow.
     *
     * @param int $percentile
     * @return static
     */
    public static function make(int $percentile): static
    {
        return new static($percentile);
    }
}
