<?php

namespace MalteKuhr\LaravelGpt\Contracts;

interface ConfidenceCalculator
{
    /**
     * Return the confidence score.
     *
     * @return int Number between 0 and 100
     */
    public function confidence(array $confidenceScores): int;
}

