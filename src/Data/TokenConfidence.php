<?php

namespace MalteKuhr\LaravelGpt\Data;

class TokenConfidence
{
    /**
     * Create a new TokenConfidence instance.
     *
     * @param string $token The token
     * @param int $confidence The confidence score for the token
     */
    public function __construct(
        public readonly string $token,
        public readonly int $confidence
    ) {}

    /**
     * Create a new TokenConfidence instance.
     *
     * @param string $token
     * @param int $confidence
     * @return static
     */
    public static function make(string $token, int $confidence): static
    {
        return new static($token, $confidence);
    }

    /**
     * Get the length of the token.
     *
     * @return int
     */
    public function length(): int
    {
        return strlen($this->token);
    }

    /**
     * Convert the TokenConfidence instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'confidence' => $this->confidence
        ];
    }

    /**
     * Create a new TokenConfidence instance from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            token: $data['token'],
            confidence: $data['confidence']
        );
    }
}
