<?php

namespace MalteKuhr\LaravelGpt\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MalteKuhr\LaravelGpt\Contracts\BaseChat;

class ChatUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        protected BaseChat $chat
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("chats.{$this->chat->getId()}")
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->getId()
        ];
    }
}
