<?php

namespace MalteKuhr\LaravelGpt\Contracts;

use MalteKuhr\LaravelGpt\Models\GptChat;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;
use MalteKuhr\LaravelGpt\Facades\ChatManager;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Models\GptChatMessage;
use RuntimeException;
use MalteKuhr\LaravelGpt\Enums\ChatType;
use MalteKuhr\LaravelGpt\Enums\ChatStatus;
use MalteKuhr\LaravelGpt\GptChat as BaseGptChat;
use MalteKuhr\LaravelGpt\GptAction as BaseGptAction;
use MalteKuhr\LaravelGpt\Helper\Dir;

abstract class BaseChat
{
    use Dir;

    /**
     * The GptChat instance associated with this trait.
     *
     * @var GptChat
     */
    protected ?GptChat $chat = null;

    /**
     * The current status of the chat.
     *
     * @var ChatStatus
     */
    public ChatStatus $status = ChatStatus::IDLE;
    
    /**
     * Find and instantiate a GptChat instance by ID and optionally class.
     *
     * @param int $id
     * @param string|null $class
     * @return static|null
     */
    public static function find(int $id, ?string $class = null): ?static
    {
        $gptChat = GptChat::where('id', $id)
            ->when($class, fn ($query, $class) => $query->where('class', $class))
            ->first();
    
        if (!$gptChat) {
            return null;
        }
    
        $properties = json_decode($gptChat->properties, true);
        
        $reflection = new \ReflectionClass($gptChat->class);
        $instance = $reflection->newInstanceWithoutConstructor();
    
        foreach ($properties as $propertyName => $value) {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
            }
        }
    
        if ($reflection->hasProperty('chat')) {
            $chatProperty = $reflection->getProperty('chat');
            $chatProperty->setAccessible(true);
            $chatProperty->setValue($instance, $gptChat);
        }
    
        return $instance;
    }

    /**
     * Save the chat and its associated messages.
     *
     * This method saves the chat instance and iterates through its messages,
     * saving any new or modified messages to the database.
     *
     * @return void
     */
    public function save(): void
    {
        // Use reflection to get all properties of the class
        $reflection = new \ReflectionClass($this);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            if ($propertyName !== 'chat') {
                $properties[$propertyName] = $property->getValue($this);
            }
        }

        // Update or create the chat record
        $attributes = [
            'class' => get_class($this),
            'type' => match (true) {
                $this instanceof BaseGptAction => ChatType::ACTION,
                $this instanceof BaseGptChat => ChatType::CHAT,
                default => throw new \InvalidArgumentException('Invalid chat type'),
            },
            'status' => $this->status ?? ChatStatus::IDLE,
            'properties' => $properties,
        ];

        // get the messages
        $messages = $this->chat->messages;

        // create or update the chat
        if ($this->chat?->id) {
            $this->chat->update($attributes);
        } else {
            $this->chat = GptChat::create($attributes);
        }

        // Save any new or modified messages
        foreach ($messages as $message) {
            if ($message->isDirty() || !$message->exists) {
                if(is_null($message->id)) {
                    unset($message->id);
                }

                $message->chat_id = $this->chat->id;
                $message->save();
            }
        }
    }

    /**
     * Add a new message to the chat.
     *
     * @param ChatMessage $message
     * @return $this
     */
    public function addMessage(ChatMessage $message): self
    {
        $gptChatMessage = new GptChatMessage(GptChatMessage::fromChatMessage($message)->toArray());

        if (is_null($this->chat)) {
            $this->chat = new GptChat();
            $this->chat->setRelation('messages', collect());
        }

        $this->chat->messages->push($gptChatMessage);
        return $this;
    }

    /**
     * Update an existing message in the chat.
     *
     * @param int $id
     * @param ChatMessage $message
     * @return $this
     */
    public function updateMessage(int $id, ChatMessage $message): self
    {
        $existingMessage = $this->chat->messages->where('id', $id)->first();
        if ($existingMessage) {
            $existingMessage->fill(GptChatMessage::fromChatMessage($message)->toArray());
        }
        return $this;
    }

    /**
     * Update the latest message in the chat.
     *
     * @param ChatMessage $message
     * @return $this
     */
    public function updateLatestMessage(ChatMessage $message): self
    {
        if ($this->chat && $this->chat->messages->isNotEmpty()) {
            $latestMessage = $this->chat->messages->last();
            $latestMessage->fill(GptChatMessage::fromChatMessage($message)->toArray());
        }
        return $this;
    }

    /**
     * Get the latest message from the chat.
     * 
     * @return ChatMessage|null
     */
    public function getLatestMessage(): ?ChatMessage
    {
        $messages = $this->getMessages();
        return !empty($messages) ? end($messages) : null;
    }

    /**
     * Get all messages from the chat.
     * 
     * @return ChatMessage[]
     */
    public function getMessages(): array
    {
        return $this->chat->messages->map(function ($message) {
            return $message->toChatMessage();
        })->all();
    }

    /**
     * Get the ID of the chat.
     * 
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->chat?->id;
    }

       /**
     * Get the system message for the assistant.
     * 
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return null;
    }

    /**
     * Get available functions for the assistant.
     * 
     * @return GptFunction[]|null
     */
    public function functions(): ?array
    {
        return null;
    }

    /**
     * Returns the function call behavior: 
     * - true to call any function
     * - false for no function calls
     * - a string with the function class name (e.g., SentimentGptFunction::class) for a specific function.
     * - an array of function class names (e.g., [SentimentGptFunction::class, AnotherGptFunction::class]) for multiple functions.
     * 
     * @return string[]|string|bool|null
     */
    public function functionCall(): array|string|bool|null
    {
        return null;
    }

    /**
     * Get the temperature for the response. (0 - 2)
     * 
     * @return ?float
     */
    public function temperature(): ?float
    {
        return null;
    }

    /**
     * Get the maximum token limit per request.
     * 
     * @return int|null
     */
    public function maxTokens(): ?int
    {
        return null;
    }

    /**
     * Get the model to be used for the request.
     * 
     * @return string
     */
    public function model(): string
    {
        return config('laravel-gpt.default_model');
    }
}