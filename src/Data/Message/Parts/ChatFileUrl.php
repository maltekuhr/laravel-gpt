<?php

namespace MalteKuhr\LaravelGpt\Data\Message\Parts;

use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;

class ChatFileUrl implements ChatMessagePart
{
    /**
     * @param string $url The URL of the file.
     */
    public function __construct(
        public readonly string $url
    ) {}

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
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
            url: $data['url']
        );
    }
}