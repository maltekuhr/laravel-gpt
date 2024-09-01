<?php

namespace MalteKuhr\LaravelGpt\Data\Message;

use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Contracts\ChatMessagePart;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatText;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFile;
use MalteKuhr\LaravelGpt\Data\Message\Parts\ChatFunctionCall;

class ChatMessage
{
    /**
     * @param ChatRole $role
     * @param ChatMessagePart[] $parts = []
     * @param int|null $id
     */
    public function __construct(
        public readonly ChatRole $role,
        public readonly array $parts = [],
        public readonly ?int $id = null,
    ) {}

    /**
     * Convert the message to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'role' => $this->role->value,
            'parts' => array_map(function ($part) {
                return [
                    ...$part->toArray(),
                    'type' => match (true) {
                        $part instanceof ChatText => 'text',
                        $part instanceof ChatFile => 'file',
                        $part instanceof ChatFunctionCall => 'function_call',
                        default => throw new \InvalidArgumentException('Unknown message part type'),
                    },
                ];
            }, $this->parts),
        ];
    }

    /**
     * Create a message from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            role: $data['role'] instanceof ChatRole ? $data['role'] : ChatRole::from($data['role']),
            parts: array_map(function ($partData) {
                return match ($partData['type']) {
                    'text' => ChatText::fromArray($partData),
                    'file' => ChatFile::fromArray($partData),
                    'function_call' => ChatFunctionCall::fromArray($partData),
                    default => throw new \InvalidArgumentException('Unknown message part type'),
                };
            }, $data['parts'])
        );
    }

    public function addPart(ChatMessagePart $part): static
    {
        return new static($this->role, [...$this->parts, $part]);
    }

    public function replacePart(int $index, ChatMessagePart $part): static
    {
        $parts = $this->parts;
        $parts[$index] = $part;
        return new static($this->role, $parts);
    }
}