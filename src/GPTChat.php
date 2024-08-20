<?php

namespace MalteKuhr\LaravelGPT;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MalteKuhr\LaravelGPT\Concerns\HasChat;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\Helper\Dir;
use MalteKuhr\LaravelGPT\Data\Message\ChatMessage;
use MalteKuhr\LaravelGPT\Facades\ChatManager;
use MalteKuhr\LaravelGPT\Jobs\RunChatJob;

class GPTChat extends Model
{
    use HasChat;
    use Dir;
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'gpt_chats';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'messages' => 'array',
    ];

    /**
     * Add a message to the chat.
     *
     * @param ChatMessage $message
     * @return $this
     */
    public function addMessage(ChatMessage $message): self
    {
        $messages = $this->getMessages();
        $messages[] = $message;
        $this->setMessages($messages);
        
        return $this;
    }

    /**
     * Get the messages for the chat.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return array_map(function ($message) {
            return ChatMessage::fromArray($message);
        }, $this->getAttribute('messages') ?? []);
    }

    /**
     * Set the messages for the chat.
     *
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages): self
    {
        $this->setAttribute('messages', array_map(function ($message) {
            return $message instanceof ChatMessage ? $message->toArray() : $message;
        }, $messages));

        $this->save();

        return $this;
    }

    /**
     * Start a new chat with the given message.
     * 
     * @param ChatMessage $message
     * @return static
     */
    public static function start(ChatMessage $message): static
    {
        return (new static([
            'class' => static::class,
        ]))->addMessage($message);
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
     * @return GPTFunction[]|null
     */
    public function functions(): ?array
    {
        return null;
    }

    /**
     * Returns the function call behavior: 
     * - true to call any function
     * - false for no function calls
     * - a string with the function class name (e.g., SentimentGPTFunction::class) for a specific function.
     * - an array of function class names (e.g., [SentimentGPTFunction::class, AnotherGPTFunction::class]) for multiple functions.
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

    /**
     * Run the chat through the chat manager.
     * 
     * @return void
     */
    public function run(): void
    {
        $this->status = ChatStatus::RUNNING;
        $this->save();

        RunChatJob::dispatch($this);
    }

    /**
     * Get the latest message from the chat.
     * 
     * @return ChatMessage|null
     */
    public function latestMessage(): ?ChatMessage
    {
        $messages = $this->getMessages();
        return count($messages) > 0 ? end($messages) : null;
    }

    /**
     * Replace the latest message in the chat.
     * 
     * @param ChatMessage $message The new message to replace the latest one
     * @return $this
     */
    public function replaceLatest(ChatMessage $message): self
    {
        $messages = $this->getMessages();
        if (!empty($messages)) {
            array_pop($messages);
            $messages[] = $message;
            $this->setMessages($messages);
        }
        return $this;
    }

}