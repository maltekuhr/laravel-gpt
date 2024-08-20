<?php

namespace MalteKuhr\LaravelGPT\Contracts;

interface ChatMessagePart
{
    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Create a message part from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static;
}