<?php

namespace MalteKuhr\LaravelGPT\Traits;

use MalteKuhr\LaravelGPT\Models\GPTChat;
use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use MalteKuhr\LaravelGPT\Facades\ChatManager;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Models\GPTChatMessage;
use RuntimeException;
use MalteKuhr\LaravelGPT\Enums\ChatType;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\GPTChat as BaseGPTChat;
use MalteKuhr\LaravelGPT\GPTAction as BaseGPTAction;

trait HasGPTChat
{
    /**
     * The GPTChat instance associated with this trait.
     *
     * @var GPTChat
     */
    protected GPTChat $chat;

    /**
     * The current status of the chat.
     *
     * @var ChatStatus
     */
    public ChatStatus $status = ChatStatus::IDLE;
    
    /**
     * Find and instantiate a GPTChat instance by ID and optionally class.
     *
     * @param int $id
     * @param string|null $class
     * @return static|null
     */
    public static function find(int $id, ?string $class = null): ?static
    {
        $gptChat = GPTChat::where('id', $id)
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
     * Run the chat and get the response.
     *
     * @throws RuntimeException
     * @return static
     */
    public function run(): static
    {
        $latestMessage = $this->latestMessage();

        if (!$latestMessage || $latestMessage->role !== ChatRole::USER) {
            throw new RuntimeException('The latest message must be from the user before running the chat.');
        }

        return ChatManager::send($this);
    }

    /**
     * Run the chat asynchronously.
     *
     * @return void
     */
    public function runAsync(): void
    {
        $this->save();

        dispatch(function () {
            $this->run();
            $this->save();
        });
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
                $this instanceof BaseGPTAction => ChatType::ACTION,
                $this instanceof BaseGPTChat => ChatType::CHAT,
                default => throw new \InvalidArgumentException('Invalid chat type'),
            },
            'status' => $this->status ?? ChatStatus::IDLE,
            'properties' => $properties,
        ];

        if ($this->chat->id) {
            $this->chat->update($attributes);
        } else {
            $this->chat = GPTChat::create($attributes);
        }

        // save the chat
        $this->chat->save();

        // Save any new or modified messages
        $this->chat->messages->each(function ($message) {
            if ($message->isDirty() || !$message->exists) {
                $message->save();
            }
        });
    }

    /**
     * Add a new message to the chat.
     *
     * @param ChatMessage $message
     * @return $this
     */
    public function addMessage(ChatMessage $message): self
    {
        $gptChatMessage = new GPTChatMessage(GPTChatMessage::fromChatMessage($message)->toArray());
        $gptChatMessage->chat_id = $this->chat->id;
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
            $existingMessage->fill(GPTChatMessage::fromChatMessage($message)->toArray());
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
}