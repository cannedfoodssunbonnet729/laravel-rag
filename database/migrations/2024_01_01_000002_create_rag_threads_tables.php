<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rag_threads', function (Blueprint $table) {
            $table->id();
            $table->string('model')->nullable();
            $table->string('title')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('rag_thread_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('rag_threads')->cascadeOnDelete();
            $table->string('role'); // user, assistant, system
            $table->longText('content');
            $table->unsignedInteger('tokens')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_thread_messages');
        Schema::dropIfExists('rag_threads');
    }
};
