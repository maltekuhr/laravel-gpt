<?php

namespace MalteKuhr\LaravelGPT\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GPTChat extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'gpt_chats';

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the messages associated with the chat.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(GPTChatMessage::class, 'chat_id');
    }
}