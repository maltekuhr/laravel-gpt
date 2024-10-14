<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MalteKuhr\LaravelGpt\Enums\ChatStatus;
use MalteKuhr\LaravelGpt\Enums\ChatType;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gpt_chats', function (Blueprint $table) {
            $table->id();

            $table->string('class');
            $table->enum('type', ChatType::names())->default(ChatType::CHAT->value);
            $table->enum('status', ChatStatus::names())->default(ChatStatus::IDLE->value);

            $table->json('properties')->nullable();
            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpt_chats');
    }
};
