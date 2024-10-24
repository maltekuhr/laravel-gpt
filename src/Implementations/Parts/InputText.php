<?php

namespace MalteKuhr\LaravelGpt\Implementations\Parts;

use MalteKuhr\LaravelGpt\Contracts\InputPart;

class InputText implements InputPart
{
    /**
     * @param string $text The text content of the message part.
     */
    public function __construct(
        public readonly string $text
    ) {}

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
        ];
    }

    /**
     * Create a message part from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            text: $data['text']
        );
    }
}