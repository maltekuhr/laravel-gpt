<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gpt_traces', function (Blueprint $table) {
            $table->id();

            $table->string('class');

            $table->json('input');
            $table->json('output');

            $table->json('meta')->nullable();
            $table->json('attributes')->nullable();
            $table->json('confidence')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gpt_traces');
    }
};
