<?php

namespace MalteKuhr\LaravelGpt\Contracts;

use MalteKuhr\LaravelGpt\Data\TokenConfidence;

interface ConfidenceCalculator
{
    /**
     * Return the confidence score.
     * 
     * @param TokenConfidence[] $tokens
     * @return int Number between 0 and 100
     */
    public function confidence(array $tokens): int;
}

