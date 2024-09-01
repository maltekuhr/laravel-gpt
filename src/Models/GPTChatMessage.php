<?php

namespace MalteKuhr\LaravelGpt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MalteKuhr\LaravelGpt\Enums\ChatRole;
use MalteKuhr\LaravelGpt\Data\Message\ChatMessage;

class GptChatMessage extends Model
{
    protected $guarded = [];

    protected $table = 'gpt_chat_messages';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'role' => ChatRole::class,
        'parts' => 'json',
    ];

    /**
     * Get the chat that owns the message.
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(GptChat::class, 'chat_id');
    }

    /**
     * Convert the GptChatMessage to a ChatMessage.
     *
     * @return ChatMessage
     */
    public function toChatMessage(): ChatMessage
    {
        return ChatMessage::fromArray([
            'role' => $this->role,
            'parts' => $this->parts,
        ]);
    }

    /**
     * Create a GptChatMessage from a ChatMessage.
     *
     * @param ChatMessage $chatMessage
     * @return static
     */
    public static function fromChatMessage(ChatMessage $chatMessage): static
    {
        return new static(
            $chatMessage->toArray()
        );
    }
}