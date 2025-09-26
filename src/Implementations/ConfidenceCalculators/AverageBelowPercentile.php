<?php

namespace MalteKuhr\LaravelGpt\Implementations\ConfidenceCalculators;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use MalteKuhr\LaravelGpt\Data\TokenConfidence;

class AverageBelowPercentile implements ConfidenceCalculator
{
    /**
     * @param float $percentile The percentile to calculate (0-100)
     */
    public function __construct(
        private readonly float $percentile
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
        $index = ($this->percentile / 100) * (count($confidenceScores) - 1);
        $threshold = $confidenceScores[floor($index)];

        $belowPercentileScores = array_filter($confidenceScores, fn($score) => $score <= $threshold);

        if (empty($belowPercentileScores)) {
            return 0;
        }

        $averageBelowPercentile = array_sum($belowPercentileScores) / count($belowPercentileScores);

        return min(100, max(0, (int)round($averageBelowPercentile)));
    }

    /**
     * Create a new instance of AverageBelowPercentile.
     *
     * @param float $percentile
     * @return static
     */
    public static function make(float $percentile): static
    {
        return new static($percentile);
    }
}
