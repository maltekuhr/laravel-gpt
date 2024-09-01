<?php

namespace MalteKuhr\LaravelGpt\Data\Message\Parts;

use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;
use Illuminate\Support\Facades\Storage;

class ChatFileUpload implements ChatMessagePart
{
    /**
     * @param string $path The path to the file in storage.
     * @param string|null $disk The storage disk name.
     * @param array $data Additional data about the file.
     */
    public function __construct(
        public readonly string $path,
        public readonly ?string $disk = null,
        public array $data = []
    ) {}

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'disk' => $this->disk,
            'data' => $this->data,
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
            path: $data['path'],
            disk: $data['disk'] ?? null,
            data: $data['data'] ?? []
        );
    }

    /**
     * Get the file content.
     *
     * @return string|null
     */
    public function getFile(): ?string
    {
        $storage = $this->disk ? Storage::disk($this->disk) : Storage::disk();
        return $storage->exists($this->path) ? $storage->get($this->path) : null;
    }

    /**
     * Set additional data about the file.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
}