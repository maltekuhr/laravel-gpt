<?php

namespace MalteKuhr\LaravelGpt\Implementations\ConfidenceCalculators;

use MalteKuhr\LaravelGpt\Contracts\ConfidenceCalculator;
use MalteKuhr\LaravelGpt\Data\TokenConfidence;

class Min implements ConfidenceCalculator
{
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

        $confidence = array_map(fn (TokenConfidence $token) => $token->confidence, $tokens);

        return min(100, max(0, (int) round(min($confidence))));
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
