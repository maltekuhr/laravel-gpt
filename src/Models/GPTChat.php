<?php

namespace MalteKuhr\LaravelGpt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GptChat extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'gpt_chats';

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });

        static::retrieved(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });

        static::deleting(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });
    }

    /**
     * Get the messages associated with the chat.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(GptChatMessage::class, 'chat_id');
    }
}