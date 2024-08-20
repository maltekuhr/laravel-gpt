<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MalteKuhr\LaravelGPT\Enums\ChatStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gpt_chats', function (Blueprint $table) {
            $table->id();

            $table->string('class');
            $table->string('status')->default(ChatStatus::IDLE->value);

            $table->json('messages');
            $table->json('data')->nullable();

            $table->timestamp('last_run_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpt_chats');
    }
};
