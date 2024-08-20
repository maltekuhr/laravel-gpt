<?php

namespace MalteKuhr\LaravelGPT\Data\Message\Parts;

use MalteKuhr\LaravelGPT\Contracts\ChatMessagePart;

class ChatFile implements ChatMessagePart
{
    /**
     * @param string $mimeType The MIME type of the file.
     * @param string $content Base64 encoded content of the file.
     */
    public function __construct(
        public readonly string $mimeType,
        public readonly string $content
    ) {}

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'mime_type' => $this->mimeType,
            'content' => $this->content,
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
            mimeType: $data['mime_type'],
            content: $data['content']
        );
    }
}