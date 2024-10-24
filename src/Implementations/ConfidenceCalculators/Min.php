<?php

namespace MalteKuhr\LaravelGpt\Implementations\ConfidenceCalculators;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;

class Min implements ConfidenceCalculator
{
    /**
     * Return the confidence score.
     *
     * @param array $confidenceScores
     * @return int Number between 0 and 100
     */
    public function confidence(array $confidenceScores): int
    {
        if (empty($confidenceScores)) {
            return 0;
        }

        return min(100, max(0, (int) round(min($confidenceScores))));
    }

    /**
     * Create a new instance of Min.
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }
}
