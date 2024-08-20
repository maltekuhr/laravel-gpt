<?php

namespace MalteKuhr\LaravelGPT\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MalteKuhr\LaravelGPT\GPTChat;

class ChatUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        protected GPTChat $chat
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("chats.{$this->chat->id}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->id
        ];
    }
}
