<?php

namespace MalteKuhr\LaravelGPT\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\Facades\ChatManager;
use MalteKuhr\LaravelGPT\GPTChat;

class RunChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected GPTChat $chat
    ) {}

    public function handle(): void
    {
        ChatManager::send($this->chat);
    }
}
