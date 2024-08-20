<?php

namespace MalteKuhr\LaravelGPT\Data\Message\Parts;

use MalteKuhr\LaravelGPT\Contracts\ChatMessagePart;
use MalteKuhr\LaravelGPT\Enums\FunctionCallStatus;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Exception;

class ChatFunctionCall implements ChatMessagePart
{
    /**
     * @param string|null $id
     * @param string $name
     * @param array $arguments
     * @param array|null $response
     * @param FunctionCallStatus $status
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly array $arguments,
        public ?array $response = null,
        public FunctionCallStatus $status = FunctionCallStatus::NEW
    ) {}

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'arguments' => $this->arguments,
            'response' => $this->response,
            'status' => $this->status->value,
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
            id: $data['id'] ?? null,
            name: $data['name'],
            arguments: $data['arguments'],
            response: $data['response'],
            status: FunctionCallStatus::from($data['status'] ?? FunctionCallStatus::NEW->value)
        );
    }

    /**
     * Set the response for the chat function call.
     *
     * @param array|null $response
     * @return $this
     */
    public function setResponse(?array $response): static
    {
        $this->response = $response;
        $this->status = FunctionCallStatus::COMPLETED;
        return $this;
    }

    /**
     * Set the exception for the chat function call.
     *
     * @param Exception $exception
     * @return $this
     */
    public function handleException(Exception $exception): static
    {
        $this->status = FunctionCallStatus::ERROR;
        $this->response = ['error' => $exception instanceof ValidationException ? Arr::flatten($exception->errors()) : $exception->getMessage()];

        return $this;
    }

    /**
     * Update the status of the chat function call.
     *
     * @param FunctionCallStatus $status
     * @return $this
     */
    public function updateStatus(FunctionCallStatus $status): static
    {
        $this->status = $status;
        return $this;
    }
}