<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MalteKuhr\LaravelGpt\Enums\ChatRole;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gpt_chat_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chat_id')->constrained('gpt_chats');

            $table->enum('rating', ['positive', 'negative'])->nullable();
            $table->text('feedback')->nullable();

            $table->enum('role', ChatRole::names());
            $table->json('parts');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpt_chat_messages');
    }
};
