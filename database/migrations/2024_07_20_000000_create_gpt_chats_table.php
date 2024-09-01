<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;
use MalteKuhr\LaravelGPT\Enums\ChatType;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gpt_chat_messages', function (Blueprint $table) {
            $table->id();

            $table->string('class');
            $table->enum('type', ChatType::names())->default(ChatType::CHAT->value);
            $table->enum('status', ChatStatus::names())->default(ChatStatus::IDLE->value);

            $table->json('properties')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpt_chat_messages');
    }
};
